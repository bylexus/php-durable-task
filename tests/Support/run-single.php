<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\Runner;
use ByLexus\TaskRunner\RunnerConfiguration;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$dsn = getenv('TEST_DATABASE_DSN') ?: null;
$user = getenv('TEST_DATABASE_USER') ?: null;
$password = getenv('TEST_DATABASE_PASSWORD') ?: null;

if ($dsn === null || $user === null || $password === null) {
    fwrite(STDERR, "Missing PostgreSQL test environment variables.\n");
    exit(1);
}

$tableName = $argv[1] ?? null;
$markerPath = $argv[2] ?? null;

if ($tableName === null || $markerPath === null) {
    fwrite(STDERR, "Usage: php tests/Support/run-single.php <table-name> <marker-path>\n");
    exit(1);
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pgsqlPdoClass = 'Pdo\\Pgsql';

if (class_exists($pgsqlPdoClass)) {
    $pdo = new $pgsqlPdoClass($dsn, $user, $password, $options);
} else {
    $pdo = new PDO($dsn, $user, $password, $options);
}

$runner = new Runner(
    $pdo,
    new QueueConfiguration($tableName),
    new RunnerConfiguration('runner-single-process'),
);

$runner->runSingle();
file_put_contents($markerPath, "stopped\n");
