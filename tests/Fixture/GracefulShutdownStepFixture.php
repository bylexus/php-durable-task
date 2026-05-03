<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class GracefulShutdownStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        usleep(1_500_000);

        return StepResult::succeeded(['completedAfterSignal' => true], 'Execution completed after shutdown request.');
    }
}
