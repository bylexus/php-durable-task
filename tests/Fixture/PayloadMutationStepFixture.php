<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class PayloadMutationStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        $details = $task->getPayload('details');
        $details->bar = 'somevalue';

        return StepResult::succeeded(meta: ['mutated' => true]);
    }
}
