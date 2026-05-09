<?php

declare(strict_types=1);

namespace ByLexus\DurableTask;

use ByLexus\DurableTask\Metadata\MetadataResolver;
use ByLexus\DurableTask\Queue\QueueConfiguration;
use ByLexus\DurableTask\Queue\QueueRecord;
use ByLexus\DurableTask\Queue\SchemaManager;

/**
 * Wraps a queue connection context.
 *
 * Stores the shared PDO connection and queue configuration so application code can reuse them across
 * enqueueing, runner creation, and schema management.
 *
 * This file is part of bylexus/durable-task
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class QueueContext {
    private \PDO $connection;
    private QueueConfiguration $queueConfiguration;

    public function __construct(\PDO $connection, ?QueueConfiguration $queueConfiguration = null) {
        $this->connection = $connection;
        $this->queueConfiguration = $queueConfiguration ?? new QueueConfiguration();
    }

    public function getConnection(): \PDO {
        return $this->connection;
    }

    public function getQueueConfiguration(): QueueConfiguration {
        return $this->queueConfiguration;
    }

    public function enqueue(
        Task $task,
        int $priority = Task::PRIO_NORMAL,
        ?MetadataResolver $metadataResolver = null,
    ): QueueRecord {
        return $task->enqueue($this->connection, $priority, $this->queueConfiguration, $metadataResolver);
    }

    public function createRunner(
        ?RunnerConfiguration $runnerConfiguration = null,
        ?MetadataResolver $metadataResolver = null,
    ): Runner {
        return new Runner(
            $this->connection,
            $this->queueConfiguration,
            $runnerConfiguration,
            $metadataResolver,
        );
    }

    public function createSchemaManager(): SchemaManager {
        return new SchemaManager($this->connection, $this->queueConfiguration);
    }

    public function bootstrapSchema(): void {
        $this->createSchemaManager()->bootstrap();
    }

    public function validateSchema(): void {
        $this->createSchemaManager()->validate();
    }

    public function tableExists(): bool {
        return $this->createSchemaManager()->tableExists();
    }

    public function blobTableExists(): bool {
        return $this->createSchemaManager()->blobTableExists();
    }

    public function exportDdl(): string {
        return SchemaManager::exportDdl($this->queueConfiguration);
    }
}
