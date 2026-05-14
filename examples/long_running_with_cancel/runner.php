<?php

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\TaskEnvironment;
use ByLexus\TaskRunner\RunnerConfiguration;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/ProcessLargeTask.php');
require_once(__DIR__ . '/ProcessLargeImportStep.php');

// Postgres:
$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=tr_test", 'postgres', 'postgres');
$qc = new QueueConfiguration(schemaName: 'phptr');

// Mysql:
// $conn = new PDO("mysql:host=127.0.0.1;port=3307;dbname=tr_test", 'phptr', 'phptr');
// $qc = new QueueConfiguration(schemaName: 'tr_test');

// sqlite:
// $conn = new PDO("sqlite:sqlite-test.db");
// $qc = new QueueConfiguration(schemaName: 'tr_test');

$runnerConfig = new RunnerConfiguration(
    bootstrapSchemaOnStart: true,
);
$env = new TaskEnvironment($conn, $qc, runnerConfiguration: $runnerConfig);
$runner = $env->createRunner();

$runner->runLoop();
