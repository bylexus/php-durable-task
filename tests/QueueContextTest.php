<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\Metadata\MetadataResolver;
use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\Queue\QueueRecord;
use ByLexus\TaskRunner\Queue\SchemaManager;
use ByLexus\TaskRunner\QueueContext;
use ByLexus\TaskRunner\RunnerConfiguration;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use PHPUnit\Framework\TestCase;

final class QueueContextTest extends TestCase
{
    public function testQueueContextExposesConfiguredConnectionAndQueueConfiguration(): void {
        $connection = $this->createStub(\PDO::class);
        $configuration = new QueueConfiguration('custom_queue', 'custom_schema');

        $context = new QueueContext($connection, $configuration);

        self::assertSame($connection, $context->getConnection());
        self::assertSame($configuration, $context->getQueueConfiguration());
    }

    public function testQueueContextEnqueueDelegatesToTaskWithStoredQueueContext(): void {
        $connection = $this->createStub(\PDO::class);
        $configuration = new QueueConfiguration('custom_queue', 'custom_schema');
        $metadataResolver = new MetadataResolver();
        $expectedRecord = new QueueRecord(
            42,
            'task-class',
            'step-class',
            'queued',
            0,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            null,
            null,
            null,
            'queued',
            0,
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
            Task::PRIO_HIGH,
        );
        $task = new class($expectedRecord) extends Task {
            public ?\PDO $receivedConnection = null;
            public ?QueueConfiguration $receivedConfiguration = null;
            public ?MetadataResolver $receivedMetadataResolver = null;
            public ?int $receivedPriority = null;
            private QueueRecord $record;

            public function __construct(QueueRecord $record) {
                parent::__construct();
                $this->record = $record;
            }

            public function nextStep(?Step $actStep = null): ?Step {
                return null;
            }

            public function enqueue(
                \PDO $connection,
                int $priority = self::PRIO_NORMAL,
                ?QueueConfiguration $configuration = null,
                ?MetadataResolver $metadataResolver = null,
            ): QueueRecord {
                $this->receivedConnection = $connection;
                $this->receivedPriority = $priority;
                $this->receivedConfiguration = $configuration;
                $this->receivedMetadataResolver = $metadataResolver;

                return $this->record;
            }
        };

        $context = new QueueContext($connection, $configuration);
        $record = $context->enqueue($task, Task::PRIO_HIGH, $metadataResolver);

        self::assertSame($expectedRecord, $record);
        self::assertSame($connection, $task->receivedConnection);
        self::assertSame(Task::PRIO_HIGH, $task->receivedPriority);
        self::assertSame($configuration, $task->receivedConfiguration);
        self::assertSame($metadataResolver, $task->receivedMetadataResolver);
    }

    public function testQueueContextCreateRunnerReusesStoredConnectionAndConfiguration(): void {
        $connection = $this->createStub(\PDO::class);
        $configuration = new QueueConfiguration('custom_queue');
        $runnerConfiguration = new RunnerConfiguration('runner-test');
        $metadataResolver = new MetadataResolver();

        $context = new QueueContext($connection, $configuration);
        $runner = $context->createRunner($runnerConfiguration, $metadataResolver);

        self::assertSame($connection, $this->readPrivateProperty($runner, 'connection'));
        self::assertSame($configuration, $this->readPrivateProperty($runner, 'queueConfiguration'));
        self::assertSame($runnerConfiguration, $this->readPrivateProperty($runner, 'runnerConfiguration'));
        self::assertSame($metadataResolver, $this->readPrivateProperty($runner, 'metadataResolver'));
    }

    public function testQueueContextCreatesSchemaHelpersFromStoredQueueConfiguration(): void {
        $connection = $this->createStub(\PDO::class);
        $configuration = new QueueConfiguration('custom_queue', 'custom_schema');
        $context = new QueueContext($connection, $configuration);

        $schemaManager = $context->createSchemaManager();
        $ddl = $context->exportDdl();

        self::assertInstanceOf(SchemaManager::class, $schemaManager);
        self::assertSame($connection, $this->readPrivateProperty($schemaManager, 'connection'));
        self::assertSame($configuration, $this->readPrivateProperty($schemaManager, 'configuration'));
        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS "custom_schema"', $ddl);
        self::assertStringContainsString(
            'CREATE TABLE IF NOT EXISTS "custom_schema"."custom_queue"',
            $ddl,
        );
    }

    private function readPrivateProperty(object $object, string $propertyName): mixed {
        $reflection = new \ReflectionProperty($object, $propertyName);

        return $reflection->getValue($object);
    }
}
