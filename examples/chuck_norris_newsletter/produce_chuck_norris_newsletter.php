<?php

namespace ByLexus\TaskRunner\Examples\chuck_norris_newsletter;

use ByLexus\TaskRunner\Examples\Support\ExampleServiceContainer;
use ByLexus\TaskRunner\TaskEnvironment;
use PHPMailer\PHPMailer\PHPMailer;

require_once(__DIR__ . '/../../vendor/autoload.php');


$container = new ExampleServiceContainer();

// See ExampleServiceContainer::createTaskEnvironment for details how to create an environment:
$env = $container->get(TaskEnvironment::class);

// Create and enqueue a new Task:
$task = new ChuckNorrisNewsletterTask($container->get(PHPMailer::class));
$task->setTo('alex@alexi.ch');
$task->setFrom('chuck@norris.com');
$env->enqueue($task);
