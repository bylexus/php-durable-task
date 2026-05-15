<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Examples\daily_cat_mail\DailyCatTask;
use ByLexus\TaskRunner\Examples\Support\ExampleServiceContainer;
use ByLexus\TaskRunner\TaskEnvironment;
use PHPMailer\PHPMailer\PHPMailer;

require_once(__DIR__ . '/../../vendor/autoload.php');

$container = new ExampleServiceContainer();

// See ExampleServiceContainer::createTaskEnvironment for details how to create an environment:
$env = $container->get(TaskEnvironment::class);

$task = new DailyCatTask($container->get(PHPMailer::class));
$task->setTo([
    'alex@alexi.ch',
    'blex@blexi.ch',
    'clex@clexi.ch',
    'dlex@dlexi.ch',
]);
$task->setFrom('cat@caas.com');
$env->enqueue($task);

print_r($task);
