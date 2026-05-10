<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Queue;

/**
 * Configures the queue table.
 *
 * Defines the PostgreSQL table name and related settings used by the task queue.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class QueueConfiguration
{
    public const DEFAULT_TABLE_NAME = 'phptr_task_queue';
    public const BLOB_TABLE_SUFFIX = '_blob_data';

    private string $tableName;
    private ?string $schemaName;

    public function __construct(string $tableName = self::DEFAULT_TABLE_NAME, ?string $schemaName = null) {
        $this->tableName = $tableName;
        $this->schemaName = $schemaName;
    }

    public function getTableName(): string {
        return $this->tableName;
    }

    public function getSchemaName(): ?string {
        return $this->schemaName;
    }

    public function getBlobTableName(): string {
        return $this->tableName . self::BLOB_TABLE_SUFFIX;
    }
}
