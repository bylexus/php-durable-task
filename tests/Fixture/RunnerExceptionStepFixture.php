<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class RunnerExceptionStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        throw new \RuntimeException('Step exploded.');
    }
}
