<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Integration;

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Enum\TaskStatus;
use ByLexus\DurableTask\Exception\QueueException;
use ByLexus\DurableTask\Queue\PostgresQueue;
use ByLexus\DurableTask\Queue\QueueConfiguration;
use ByLexus\DurableTask\Queue\SchemaManager;
use ByLexus\DurableTask\Task;
use ByLexus\DurableTask\Tests\Fixture\QueueWorkflowTaskFixture;
use ByLexus\DurableTask\Tests\Support\PostgresIntegrationConnection;
use PHPUnit\Framework\TestCase;

final class PostgresQueueIntegrationTest extends TestCase
{
    public function testEnqueueCreatesQueuedRecordAndEmitsNotification(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $listener = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $queue = new PostgresQueue($pdo, $configuration);
            $listener->exec(sprintf('LISTEN "%s"', $queue->getNotificationChannel()));

            $task = new QueueWorkflowTaskFixture();
            $task->setPayload(['job' => 'alpha']);
            $record = $task->enqueue($pdo, $configuration);

            self::assertNotNull($record->taskId);
            self::assertSame(TaskStatus::QUEUED->value, $record->taskStatus);
            self::assertSame(StepStatus::QUEUED->value, $record->stepStatus);
            self::assertEquals((object) ['job' => 'alpha'], $record->payload);

            $notification = $this->fetchNotification($listener);

            self::assertIsArray($notification);
            self::assertSame($queue->getNotificationChannel(), $notification['message']);
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testClaimReturnsAtMostOneRecordAcrossConnections(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $otherPdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $task = new QueueWorkflowTaskFixture();
            $task->setPayload(['job' => 'beta']);
            $task->enqueue($pdo, $configuration);

            $firstQueue = new PostgresQueue($pdo, $configuration);
            $secondQueue = new PostgresQueue($otherPdo, $configuration);

            $claimed = $firstQueue->claim('runner-1');
            $secondClaim = $secondQueue->claim('runner-2');

            self::assertNotNull($claimed);
            self::assertSame(TaskStatus::RUNNING->value, $claimed->taskStatus);
            self::assertSame(StepStatus::RUNNING->value, $claimed->stepStatus);
            self::assertSame('runner-1', $claimed->claimedBy);
            self::assertNull($secondClaim);

            $task = Task::fromQueueRecord($claimed);

            self::assertInstanceOf(QueueWorkflowTaskFixture::class, $task);
            self::assertEquals((object) ['job' => 'beta'], $task->getPayload());
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testEnqueueNormalizesMissingPayloadToObject(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $task = new QueueWorkflowTaskFixture();
            $record = $task->enqueue($pdo, $configuration);

            self::assertInstanceOf(\stdClass::class, $record->payload);

            $rehydratedTask = Task::fromQueueRecord($record);

            self::assertInstanceOf(\stdClass::class, $rehydratedTask->getPayload());
            self::assertInstanceOf(QueueWorkflowTaskFixture::class, $rehydratedTask);
            self::assertInstanceOf(
                \ByLexus\DurableTask\Tests\Fixture\QueueWorkflowStepFixture::class,
                $rehydratedTask->actualStep(),
            );
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testDeleteExpiredRemovesOnlyExpiredTerminalRows(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $queue = new PostgresQueue($pdo, $configuration);
            $expiredTaskId = $this->enqueueTask($pdo, $configuration, ['job' => 'expired'])->taskId;
            $futureTaskId = $this->enqueueTask($pdo, $configuration, ['job' => 'future'])->taskId;
            $queuedTaskId = $this->enqueueTask($pdo, $configuration, ['job' => 'queued'])->taskId;

            self::assertNotNull($expiredTaskId);
            self::assertNotNull($futureTaskId);
            self::assertNotNull($queuedTaskId);

            $past = new \DateTimeImmutable('-1 hour');
            $future = new \DateTimeImmutable('+1 hour');

            $this->updateTask(
                $pdo,
                $queue,
                $expiredTaskId,
                [
                    'task_status' => TaskStatus::SUCCEEDED,
                    'step_status' => StepStatus::SUCCEEDED,
                    'task_finished_at' => $past,
                    'step_finished_at' => $past,
                    'cleanup_at' => $past,
                ],
            );
            $this->updateTask(
                $pdo,
                $queue,
                $futureTaskId,
                [
                    'task_status' => TaskStatus::FAILED,
                    'step_status' => StepStatus::FAILED,
                    'task_finished_at' => $past,
                    'step_finished_at' => $past,
                    'cleanup_at' => $future,
                ],
            );
            $this->updateTask(
                $pdo,
                $queue,
                $queuedTaskId,
                [
                    'cleanup_at' => $past,
                ],
            );

            self::assertSame(1, $queue->deleteExpired());
            self::assertFalse($this->taskExists($pdo, $tableName, $expiredTaskId));
            self::assertTrue($this->taskExists($pdo, $tableName, $futureTaskId));
            self::assertTrue($this->taskExists($pdo, $tableName, $queuedTaskId));
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testUpdateKeepsRowLockedUntilOuterTransactionEnds(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $otherPdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $queue = new PostgresQueue($pdo, $configuration);
            $taskId = $this->enqueueTask($pdo, $configuration, ['job' => 'locked'])->taskId;

            self::assertNotNull($taskId);

            $pdo->beginTransaction();
            $queue->update($taskId, ['cancel_requested' => true, 'cancel_reason' => 'hold lock']);

            self::assertFalse($this->canLockTaskRow($otherPdo, $tableName, $taskId));

            $pdo->commit();

            self::assertTrue($this->canLockTaskRow($otherPdo, $tableName, $taskId));
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($otherPdo->inTransaction()) {
                $otherPdo->rollBack();
            }

            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testUpdateRequiresActiveTransaction(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);
            $schemaManager->bootstrap();

            $queue = new PostgresQueue($pdo, $configuration);
            $taskId = $this->enqueueTask($pdo, $configuration, ['job' => 'tx-required'])->taskId;

            self::assertNotNull($taskId);

            $this->expectException(QueueException::class);
            $this->expectExceptionMessage('PostgresQueue::update() requires an active transaction.');

            $queue->update($taskId, ['cancel_requested' => true]);
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    private function enqueueTask(
        \PDO $pdo,
        QueueConfiguration $configuration,
        array $payload,
    ): \ByLexus\DurableTask\Queue\QueueRecord {
        $task = new QueueWorkflowTaskFixture();
        $task->setPayload($payload);

        return $task->enqueue($pdo, $configuration);
    }

    /** @return array<string, mixed> */
    private function fetchNotification(\PDO $listener): array {
        if (method_exists($listener, 'getNotify')) {
            $notification = $listener->getNotify(\PDO::FETCH_ASSOC, 1000);
        } elseif (method_exists($listener, 'pgsqlGetNotify')) {
            $notification = $listener->pgsqlGetNotify(\PDO::FETCH_ASSOC, 1000);
        } else {
            $this->markTestSkipped('The configured PDO driver does not support PostgreSQL notifications.');
        }

        if (!is_array($notification)) {
            self::fail('Expected a PostgreSQL notification but none was received.');
        }

        /** @var array<string, mixed> $notification */
        return $notification;
    }

    private function taskExists(\PDO $pdo, string $tableName, int $taskId): bool {
        $statement = $pdo->prepare(
            sprintf('SELECT EXISTS (SELECT 1 FROM "%s" WHERE task_id = :task_id)', str_replace('"', '""', $tableName)),
        );
        $statement->execute(['task_id' => $taskId]);

        return (bool) $statement->fetchColumn();
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function updateTask(\PDO $pdo, PostgresQueue $queue, int $taskId, array $changes): void {
        $pdo->beginTransaction();

        try {
            $queue->update($taskId, $changes);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    private function canLockTaskRow(\PDO $pdo, string $tableName, int $taskId): bool {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $pdo->beginTransaction();

        try {
            $statement = $pdo->prepare(
                sprintf(
                    'SELECT task_id FROM "%s" WHERE task_id = :task_id FOR UPDATE SKIP LOCKED',
                    str_replace('"', '""', $tableName),
                ),
            );
            $statement->execute(['task_id' => $taskId]);
            $row = $statement->fetch(\PDO::FETCH_ASSOC);

            $pdo->rollBack();

            return is_array($row);
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }
}
