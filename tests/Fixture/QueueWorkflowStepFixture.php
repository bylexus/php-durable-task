<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;

final class QueueWorkflowStepFixture extends Step
{
    public function execute(): StepResult {
        return StepResult::succeeded($this->getPayload(), ['executed' => true]);
    }
}
