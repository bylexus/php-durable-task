<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests;

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Enum\TaskStatus;
use ByLexus\DurableTask\Exception\ConfigurationException;
use ByLexus\DurableTask\Queue\QueueRecord;
use ByLexus\DurableTask\Task;
use ByLexus\DurableTask\Tests\Fixture\EmptyWorkflowTaskFixture;
use ByLexus\DurableTask\Tests\Fixture\QueueWorkflowStepFixture;
use ByLexus\DurableTask\Tests\Fixture\QueueWorkflowTaskFixture;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testTaskCanBeReconstitutedFromQueueRecord(): void {
        $record = new QueueRecord(
            42,
            QueueWorkflowTaskFixture::class,
            QueueWorkflowStepFixture::class,
            TaskStatus::QUEUED->value,
            1,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            null,
            null,
            null,
            StepStatus::QUEUED->value,
            2,
            null,
            null,
            ['foo' => 'bar'],
            null,
            null,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            null,
            null,
            null,
            null,
            false,
            null,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );

        $task = Task::fromQueueRecord($record);

        self::assertInstanceOf(QueueWorkflowTaskFixture::class, $task);
        self::assertSame(42, $task->getId());
        self::assertSame(['foo' => 'bar'], $task->getPayload());
        self::assertInstanceOf(QueueWorkflowStepFixture::class, $task->actualStep());
        self::assertSame(2, $task->actualStep()?->getStepAttempt());
    }

    public function testEnqueueRequiresInitialStep(): void {
        $task = new EmptyWorkflowTaskFixture();

        $this->expectException(ConfigurationException::class);

        $task->enqueue($this->createStub(\PDO::class));
    }

    public function testNullPayloadIsExposedAsObjectOnTaskAndStep(): void {
        $record = new QueueRecord(
            42,
            QueueWorkflowTaskFixture::class,
            QueueWorkflowStepFixture::class,
            TaskStatus::QUEUED->value,
            1,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            null,
            null,
            null,
            StepStatus::QUEUED->value,
            2,
            null,
            null,
            null,
            null,
            null,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            null,
            null,
            null,
            null,
            false,
            null,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );

        $task = Task::fromQueueRecord($record);

        self::assertNull($task->getStoredPayload());
        self::assertInstanceOf(\stdClass::class, $task->getPayload());
        self::assertInstanceOf(\stdClass::class, $task->actualStep()?->getPayload());
        self::assertNull($task->actualStep()?->getStoredPayload());
    }
}
