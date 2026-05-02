<?php

use ByLexus\DurableTask\Queue\SchemaManager;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/ChuckNorrisNewsletterTask.php');


$conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=durable_task_test", 'postgres', 'postgres');
$sm = new SchemaManager($conn);
$sm->bootstrap();

$task = new ChuckNorrisNewsletterTask();
$task->setTo('alex@alexi.ch');
$task->setFrom('chuck@norris.com');
$task->enqueue($conn);
