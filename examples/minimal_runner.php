<?php

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\Runner;
use ByLexus\TaskRunner\RunnerConfiguration;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../vendor/autoload.php');

$container = new ExampleServiceContainer();
$qc = new QueueConfiguration(schemaName: 'phptr');
$runnerConfig = new RunnerConfiguration(
    bootstrapSchemaOnStart: true,
    container: $container,
    logger: $container->get(LoggerInterface::class)
);
$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=tr_test", 'postgres', 'postgres');
$runner = new Runner(connection: $conn, runnerConfiguration: $runnerConfig, queueConfiguration: $qc);

// $runner->runLoop();
$runner->runSingle();
