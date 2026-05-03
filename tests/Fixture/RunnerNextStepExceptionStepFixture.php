<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class RunnerNextStepExceptionStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        $task->getPayload()->stepCompleted = true;

        return StepResult::succeeded(meta: ['executed' => true]);
    }
}
