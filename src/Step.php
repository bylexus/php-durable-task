<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner;

use ByLexus\TaskRunner\Result\StepResult;

/**
 * Workflow step contract.
 *
 * A Step encapsulates a single unit of executable work in a Task workflow.
 * Implementations are stateless from the queue's perspective: all persisted
 * state lives on the Task. Step metadata (Retries, RetryMode, MaxRuntime) is
 * read from PHP attributes on the implementing class.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
interface Step {
    public function execute(Task $task): StepResult;
}
