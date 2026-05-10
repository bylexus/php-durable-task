<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\Queue\SchemaManager;

require dirname(__DIR__) . '/vendor/autoload.php';

$tableName = $argv[1] ?? QueueConfiguration::DEFAULT_TABLE_NAME;
$schemaName = $argv[2] ?? null;

fwrite(STDOUT, SchemaManager::exportDdl(new QueueConfiguration($tableName, $schemaName)));
