<?php

namespace ByLexus\TaskRunner\Examples\Support;

use Psr\Log\AbstractLogger;
use Stringable;

class ConsoleLogger extends AbstractLogger {
    public function log($level, string|Stringable $message, array $context = []): void {
        $ts = date(DATE_W3C);
        $context = json_encode($context);
        fwrite(STDERR, "{$ts} [{$level}]: {$message} :: {$context}\n");
    }
}
