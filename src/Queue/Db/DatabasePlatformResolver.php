<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Queue\Db;

use ByLexus\TaskRunner\Exception\ConfigurationException;

final class DatabasePlatformResolver {
    public static function resolve(\PDO $connection): DatabasePlatform {
        $driverName = self::readAttribute($connection, \PDO::ATTR_DRIVER_NAME);

        if (!is_string($driverName) || $driverName === '') {
            return new PostgresPlatform();
        }

        return match ($driverName) {
            'pgsql' => new PostgresPlatform(),
            'mysql' => self::resolveMySqlFamily($connection),
            'sqlite' => new SqlitePlatform(),
            default => throw new ConfigurationException(
                sprintf('Unsupported PDO driver for queue storage: %s', $driverName),
            ),
        };
    }

    private static function resolveMySqlFamily(\PDO $connection): DatabasePlatform {
        $serverVersion = self::readAttribute($connection, \PDO::ATTR_SERVER_VERSION);

        if (is_string($serverVersion) && stripos($serverVersion, 'MariaDB') !== false) {
            return new MariaDbPlatform();
        }

        return new MySqlPlatform();
    }

    private static function readAttribute(\PDO $connection, int $attribute): mixed {
        try {
            return $connection->getAttribute($attribute);
        } catch (\Throwable) {
            return null;
        }
    }
}
