<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Integration;

use ByLexus\DurableTask\Queue\QueueConfiguration;
use ByLexus\DurableTask\Queue\SchemaManager;
use ByLexus\DurableTask\Tests\Support\PostgresIntegrationConnection;
use PHPUnit\Framework\TestCase;

final class SchemaManagerIntegrationTest extends TestCase
{
    public function testBootstrapCreatesSchemaIdempotently(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);

            $schemaManager->bootstrap();
            $schemaManager->bootstrap();

            self::assertTrue($schemaManager->tableExists());
            self::assertTrue($this->columnExists($pdo, $tableName, 'cleanup_at'));
            self::assertTrue($this->columnAllowsNulls($pdo, $tableName, 'payload_json'));
            self::assertTrue($this->taskIdIsIdentityColumn($pdo, $tableName));
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testExportedDdlCanCreateSchemaExplicitly(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $ddl = SchemaManager::exportDdl(new QueueConfiguration($tableName));

            foreach ($this->statementsFromDdl($ddl) as $statement) {
                $pdo->exec($statement);
            }

            $schemaManager = new SchemaManager($pdo, new QueueConfiguration($tableName));

            self::assertTrue($schemaManager->tableExists());
            $schemaManager->validate();
            self::assertTrue($this->columnExists($pdo, $tableName, 'cleanup_at'));
            self::assertTrue($this->columnAllowsNulls($pdo, $tableName, 'payload_json'));
            self::assertTrue($this->taskIdIsIdentityColumn($pdo, $tableName));
            self::assertTrue($this->indexExists($pdo, sprintf('%s_cleanup_at_idx', $tableName)));
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    public function testBootstrapCreatesRequiredIndexes(): void {
        $pdo = PostgresIntegrationConnection::requirePdo($this);
        $tableName = PostgresIntegrationConnection::uniqueTableName();

        try {
            $configuration = new QueueConfiguration($tableName);
            $schemaManager = new SchemaManager($pdo, $configuration);

            $schemaManager->bootstrap();

            self::assertTrue($this->indexExists($pdo, sprintf('%s_cleanup_at_idx', $tableName)));
            self::assertTrue($this->indexExists($pdo, sprintf('%s_task_status_available_at_idx', $tableName)));
        } finally {
            PostgresIntegrationConnection::dropTableIfExists($pdo, $tableName);
        }
    }

    private function columnExists(\PDO $pdo, string $tableName, string $columnName): bool {
        $statement = $pdo->prepare(
            'SELECT EXISTS (
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table_name
                  AND column_name = :column_name
            )',
        );
        $statement->execute([
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return (bool) $statement->fetchColumn();
    }

    private function taskIdIsIdentityColumn(\PDO $pdo, string $tableName): bool {
        $statement = $pdo->prepare(
            'SELECT is_identity, identity_generation
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table_name
                  AND column_name = :column_name',
        );
        $statement->execute([
            'table_name' => $tableName,
            'column_name' => 'task_id',
        ]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return false;
        }

        return $row['is_identity'] === 'YES' && $row['identity_generation'] === 'BY DEFAULT';
    }

    private function columnAllowsNulls(\PDO $pdo, string $tableName, string $columnName): bool {
        $statement = $pdo->prepare(
            'SELECT is_nullable
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table_name
                  AND column_name = :column_name',
        );
        $statement->execute([
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return $statement->fetchColumn() === 'YES';
    }

    private function indexExists(\PDO $pdo, string $indexName): bool {
        $statement = $pdo->prepare(
            'SELECT EXISTS (
                SELECT 1
                FROM pg_indexes
                WHERE schemaname = current_schema()
                  AND indexname = :index_name
            )',
        );
        $statement->execute(['index_name' => $indexName]);

        return (bool) $statement->fetchColumn();
    }

    /** @return list<string> */
    private function statementsFromDdl(string $ddl): array {
        $normalizedDdl = rtrim($ddl);

        if (str_ends_with($normalizedDdl, ';')) {
            $normalizedDdl = substr($normalizedDdl, 0, -1);
        }

        return array_values(
            array_filter(
                array_map('trim', explode(";\n\n", $normalizedDdl)),
                static fn (string $statement): bool => $statement !== '',
            ),
        );
    }
}
