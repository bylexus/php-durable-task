<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class RunnerNextStepExceptionTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new \ByLexus\TaskRunner\Tests\Fixture\RunnerNextStepExceptionStepFixture();
        }

        throw new \RuntimeException('nextStep exploded.');
    }
}
