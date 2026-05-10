<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class RunnerTimeoutTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new RunnerTimeoutStepFixture();
        }

        return null;
    }
}
