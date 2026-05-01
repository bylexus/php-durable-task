<?php

declare(strict_types=1);

use ByLexus\DurableTask\Queue\QueueConfiguration;
use ByLexus\DurableTask\Runner;
use ByLexus\DurableTask\RunnerConfiguration;

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
$connectionMode = $argv[3] ?? 'auto';
$timeoutSeconds = isset($argv[4]) ? (int) $argv[4] : 1;
$readyPath = $argv[5] ?? null;

if ($tableName === null || $markerPath === null) {
    fwrite(STDERR, "Usage: php tests/Support/run-loop.php <table-name> <marker-path> [connection-mode] [timeout-seconds] [ready-path]\n");
    exit(1);
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pgsqlPdoClass = 'Pdo\\Pgsql';
$pdo = match ($connectionMode) {
    'plain-pdo' => new PDO($dsn, $user, $password, $options),
    'pdo-pgsql' => new $pgsqlPdoClass($dsn, $user, $password, $options),
    default => class_exists($pgsqlPdoClass)
        ? new $pgsqlPdoClass($dsn, $user, $password, $options)
        : new PDO($dsn, $user, $password, $options),
};

$runner = new Runner(
    $pdo,
    new QueueConfiguration($tableName),
    new RunnerConfiguration('runner-loop-process', false, $timeoutSeconds),
);

if ($readyPath !== null) {
    file_put_contents($readyPath, "ready\n");
}

$runner->runLoop();
file_put_contents($markerPath, "stopped\n");
