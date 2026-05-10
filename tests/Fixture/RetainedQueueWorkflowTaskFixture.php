<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

#[CleanupAfter(new \DateInterval('PT30S'), new \DateInterval('P7D'))]
final class RetainedQueueWorkflowTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new QueueWorkflowStepFixture();
        }

        return null;
    }
}
