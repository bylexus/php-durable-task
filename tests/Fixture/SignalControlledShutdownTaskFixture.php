<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use ByLexus\TaskRunner\Tests\Fixture\SignalControlledShutdownStepFixture;

final class SignalControlledShutdownTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new SignalControlledShutdownStepFixture();
        }

        return null;
    }
}
