<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class PayloadHandoffTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new PayloadHandoffSourceStepFixture();
        }

        if ($actStep instanceof PayloadHandoffSourceStepFixture) {
            return new PayloadHandoffTargetStepFixture();
        }

        return null;
    }
}
