<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Examples\long_running_with_cancel\ProcessLargeTask;
use ByLexus\TaskRunner\Examples\Support\ExampleServiceContainer;
use ByLexus\TaskRunner\TaskEnvironment;

require_once(__DIR__ . '/../../vendor/autoload.php');


$container = new ExampleServiceContainer();

// See ExampleServiceContainer::createTaskEnvironment for details how to create an environment:
$env = $container->get(TaskEnvironment::class);

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
