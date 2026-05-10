<?php

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\Runner;
use ByLexus\TaskRunner\RunnerConfiguration;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../vendor/autoload.php');

$container = new ExampleServiceContainer();
$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=tr_test", 'postgres', 'postgres');
$qc = new QueueConfiguration(schemaName: 'phptr');
// $qc = new QueueConfiguration(schemaName: 'tr_test');
// $qc = new QueueConfiguration();
$runnerConfig = new RunnerConfiguration(
    bootstrapSchemaOnStart: true,
    container: $container,
    logger: $container->get(LoggerInterface::class)
);
// $conn = new PDO("mysql:host=127.0.0.1;port=3306;dbname=tr_test", 'phptr', 'phptr');
// $conn = new PDO("sqlite:sqlite-test.db");
$runner = new Runner(connection: $conn, runnerConfiguration: $runnerConfig, queueConfiguration: $qc);

$runner->runLoop();
// $runner->runSingle();
