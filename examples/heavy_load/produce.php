<?php

/**
 * Example producer that creates 100 tasks that all loop 100 times - be careful!
 * It causes the workers to do a lot of work
 *
 * Useful to test parallel workers and their behaviour
 */

use ByLexus\TaskRunner\Examples\heavy_load\CounterTask;
use ByLexus\TaskRunner\Examples\Support\ExampleServiceContainer;
use ByLexus\TaskRunner\TaskEnvironment;

require_once __DIR__ . '/../../vendor/autoload.php';

$container = new ExampleServiceContainer();

// See ExampleServiceContainer::createTaskEnvironment for details how to create an environment:
$env = $container->get(TaskEnvironment::class);

$amount = 100;
for ($i = 1; $i <= $amount; $i++) {
    $task = new CounterTask();
    $env->enqueue($task);
}

echo "Enqueued {$amount} CounterTask instances.\n";
