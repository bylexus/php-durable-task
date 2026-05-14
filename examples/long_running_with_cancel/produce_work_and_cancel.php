<?php

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\RunnerConfiguration;
use ByLexus\TaskRunner\TaskEnvironment;

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/ProcessLargeTask.php');


// Postgres:
$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=tr_test", 'postgres', 'postgres');
$qc = new QueueConfiguration(schemaName: 'phptr');

// Mysql:
// $conn = new PDO("mysql:host=127.0.0.1;port=3306;dbname=tr_test", 'phptr', 'phptr');
// $qc = new QueueConfiguration(schemaName: 'tr_test');

// sqlite:
// $conn = new PDO("sqlite:sqlite-test.db");
// $qc = new QueueConfiguration();
$runnerConfig = new RunnerConfiguration(
    bootstrapSchemaOnStart: true,
);
$env = new TaskEnvironment($conn, $qc, runnerConfiguration: $runnerConfig);
$env->getSchemaManager()->bootstrap();

$amountOfWork = 100;
$wait = 10;
$task = new ProcessLargeTask();
$task->setAmountOfWork($amountOfWork);
$env->enqueue($task);

echo "Task #{$task->getId()} started with {$amountOfWork} work items.\n";
echo "Wainting {$wait}s ...\n";
sleep($wait);
echo "Request cancellation ...\n";

$task->reload()->cancel("That took waaay too long ...");
echo "Cancellation requested\n";
