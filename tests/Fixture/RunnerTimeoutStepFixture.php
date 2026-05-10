<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\MaxRuntime;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

#[MaxRuntime(new \DateInterval('PT1S'))]
final class RunnerTimeoutStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        usleep(1_500_000);

        return StepResult::succeeded(['timedOut' => false], 'Execution completed.');
    }
}
