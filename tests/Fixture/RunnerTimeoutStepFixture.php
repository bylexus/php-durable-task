<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Attribute\MaxRuntime;
use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

#[MaxRuntime(new \DateInterval('PT1S'))]
final class RunnerTimeoutStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        usleep(1_500_000);

        return StepResult::succeeded(['timedOut' => false], 'Execution completed.');
    }
}
