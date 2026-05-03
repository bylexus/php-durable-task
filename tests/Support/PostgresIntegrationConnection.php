<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Support;

use PHPUnit\Framework\TestCase;

final class PostgresIntegrationConnection
{
    public const DSN_ENV = 'TEST_DATABASE_DSN';
    public const USER_ENV = 'TEST_DATABASE_USER';
    public const PASSWORD_ENV = 'TEST_DATABASE_PASSWORD';

    public static function requirePdo(TestCase $testCase): \PDO {
        $dsn = getenv(self::DSN_ENV) ?: null;
        $user = getenv(self::USER_ENV) ?: null;
        $password = getenv(self::PASSWORD_ENV) ?: null;

        if ($dsn === null || $user === null || $password === null) {
            $testCase->markTestSkipped(
                sprintf(
                    'Set %s, %s, and %s to run PostgreSQL integration tests.',
                    self::DSN_ENV,
                    self::USER_ENV,
                    self::PASSWORD_ENV,
                ),
            );
        }

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        $pgsqlPdoClass = 'Pdo\\Pgsql';

        if (class_exists($pgsqlPdoClass)) {
            $pdo = new $pgsqlPdoClass($dsn, $user, $password, $options);
        } else {
            $pdo = new \PDO($dsn, $user, $password, $options);
        }

        return $pdo;
    }

    public static function uniqueTableName(string $prefix = 'durable_task_queue_test'): string {
        return sprintf('%s_%s', $prefix, bin2hex(random_bytes(6)));
    }

    public static function dropTableIfExists(\PDO $pdo, string $tableName): void {
        $pdo->exec(sprintf('DROP TABLE IF EXISTS "%s" CASCADE', str_replace('"', '""', $tableName)));
    }
}
