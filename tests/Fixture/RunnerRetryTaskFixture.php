<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class RunnerRetryTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new RunnerRetryStepFixture();
        }

        return null;
    }
}
