<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class CancellingStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        $task->getPayload()->cancelledDuringExecution = true;
        $task->cancel('Cancelled during execution.');

        return StepResult::succeeded(['cancelRequested' => true]);
    }
}
