<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class PayloadHandoffTargetStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        return StepResult::succeeded();
    }
}
