<?php

/**
 * This is a minimal Queue runner that works on incoming jobs.
 * Configure a database and set the following environment variables:
 *
 * - EXAMPLE_DATABASE_DSN: PDO DSN, e.g. "pgsql:host:127.0.0.1;port=5432;dbname=tr_test"
 * - EXAMPLE_DATABASE_USER: the DB user, e.g. "postgres"
 * - EXAMPLE_DATABASE_PASSWORD: the DB Password, e.g. "postgres"
 */

namespace ByLexus\TaskRunner\Examples;

use ByLexus\TaskRunner\Examples\Support\ExampleServiceContainer;
use ByLexus\TaskRunner\TaskEnvironment;

require_once(__DIR__ . '/../vendor/autoload.php');

$container = new ExampleServiceContainer();

// See ExampleServiceContainer::createTaskEnvironment for details how to create an environment:
$env = $container->get(TaskEnvironment::class);

$runner = $env->createRunner();

// runLoop() benefits from notifications only on PostgreSQL; the other backends poll.
$runner->runLoop();

// if you just want a single run, then end:
// $runner->runSingle();
