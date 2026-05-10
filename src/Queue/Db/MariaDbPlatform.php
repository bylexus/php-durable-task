<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Queue\Db;

final class MariaDbPlatform extends MySqlPlatform {
    public function getName(): string {
        return 'mariadb';
    }
}
