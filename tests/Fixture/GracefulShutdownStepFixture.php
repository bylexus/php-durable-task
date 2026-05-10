<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class GracefulShutdownStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        usleep(1_500_000);

        return StepResult::succeeded(['completedAfterSignal' => true], 'Execution completed after shutdown request.');
    }
}
