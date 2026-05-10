<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class RunnerExceptionStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        throw new \RuntimeException('Step exploded.');
    }
}
