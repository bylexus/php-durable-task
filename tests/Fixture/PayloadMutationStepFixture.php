<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class PayloadMutationStepFixture implements Step
{
    public function execute(Task $task): StepResult {
        $details = $task->getPayload('details');
        $details->bar = 'somevalue';

        return StepResult::succeeded(meta: ['mutated' => true]);
    }
}
