<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Queue;

use ByLexus\TaskRunner\Exception\ConfigurationException;
use ByLexus\TaskRunner\Queue\Db\DatabasePlatform;
use ByLexus\TaskRunner\Queue\Db\DatabasePlatformResolver;

/**
 * Manages the queue schema.
 *
 * Creates and validates the queue schema required by the task runner.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class SchemaManager {
    /** @var list<string> */
    private const REQUIRED_COLUMNS = [
        'task_id',
        'task_class',
        'step_class',
        'task_status',
        'priority',
        'task_created_at',
        'task_started_at',
        'task_finished_at',
        'cleanup_at',
        'step_status',
        'step_attempt',
        'step_started_at',
        'step_finished_at',
        'payload_json',
        'result_json',
        'error_json',
        'available_at',
        'claimed_at',
        'claimed_by',
        'last_error_code',
        'last_error_message',
        'cancel_requested',
        'cancel_reason',
        'updated_at',
    ];

    /** @var list<string> */
    private const REQUIRED_BLOB_COLUMNS = [
        'blob_id',
        'task_id',
        'content',
        'size_bytes',
        'sha256',
        'created_at',
    ];

    private \PDO $connection;
    private \ByLexus\TaskRunner\Queue\QueueConfiguration $configuration;
    private DatabasePlatform $platform;

    public function __construct(
        \PDO $connection,
        ?\ByLexus\TaskRunner\Queue\QueueConfiguration $configuration = null,
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration ?? new \ByLexus\TaskRunner\Queue\QueueConfiguration();
        $this->platform = DatabasePlatformResolver::resolve($this->connection);
        $this->platform->validateConfiguration($this->configuration);
    }

    /** @return list<string> */
    private function bootstrapStatements(): array {
        return $this->platform->bootstrapSchemaStatements($this->connection, $this->configuration);
    }

    /** @return list<string> */
    private function exportStatements(): array {
        return $this->platform->exportSchemaStatements($this->configuration);
    }

    public function exportDdl(): string {
        return implode(";\n\n", $this->exportStatements()) . ";\n";
    }

    public function bootstrap(): void {
        foreach ($this->bootstrapStatements() as $statement) {
            $this->connection->exec($statement);
        }

        $this->validate();
    }

    public function validate(): void {
        $columns = $this->fetchColumnNames($this->configuration->getTableName());
        $missingColumns = array_values(array_diff(self::REQUIRED_COLUMNS, $columns));

        if ($missingColumns !== []) {
            throw new ConfigurationException(
                sprintf(
                    'Queue table %s is missing required columns: %s',
                    $this->configuration->getTableName(),
                    implode(', ', $missingColumns),
                ),
            );
        }

        $blobColumns = $this->fetchColumnNames($this->configuration->getBlobTableName());
        $missingBlobColumns = array_values(array_diff(self::REQUIRED_BLOB_COLUMNS, $blobColumns));

        if ($missingBlobColumns !== []) {
            throw new ConfigurationException(
                sprintf(
                    'Attachment blob table %s is missing required columns: %s',
                    $this->configuration->getBlobTableName(),
                    implode(', ', $missingBlobColumns),
                ),
            );
        }
    }

    public function tableExists(): bool {
        return $this->schemaTableExists($this->configuration->getTableName());
    }

    public function blobTableExists(): bool {
        return $this->schemaTableExists($this->configuration->getBlobTableName());
    }

    private function schemaTableExists(string $tableName): bool {
        return $this->platform->tableExists($this->connection, $this->configuration, $tableName);
    }

    /** @return list<string> */
    private function fetchColumnNames(string $tableName): array {
        return $this->platform->fetchColumnNames($this->connection, $this->configuration, $tableName);
    }
}
