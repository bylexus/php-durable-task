<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Queue;

final class QueueConfiguration
{
    public const DEFAULT_TABLE_NAME = 'durable_task_queue';

    private string $tableName;

    public function __construct(string $tableName = self::DEFAULT_TABLE_NAME) {
        $this->tableName = $tableName;
    }

    public function getTableName(): string {
        return $this->tableName;
    }
}
