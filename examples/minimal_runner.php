<?php

use ByLexus\DurableTask\Queue\QueueConfiguration;
use ByLexus\DurableTask\Metadata\MetadataResolver;
use ByLexus\DurableTask\Queue\SchemaManager;
use ByLexus\DurableTask\Runner;
use ByLexus\DurableTask\RunnerConfiguration;

require_once(__DIR__ . '/../vendor/autoload.php');

class MinimalRunner extends Runner {
    public function __construct(PDO $connection, ?QueueConfiguration $queueConfiguration = null, ?RunnerConfiguration $runnerConfiguration = null, ?MetadataResolver $metadataResolver = null) {
        parent::__construct($connection, $queueConfiguration, $runnerConfiguration, $metadataResolver);

        $sm = new SchemaManager($connection);
        $sm->bootstrap();
    }
}

$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=durable_task_test", 'postgres', 'postgres');
$runner = new MinimalRunner($conn);

$runner->runLoop();
