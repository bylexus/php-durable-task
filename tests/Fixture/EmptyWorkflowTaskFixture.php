<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

final class EmptyWorkflowTaskFixture extends Task
{
    public function nextStep(?Step $actStep = null): ?Step {
        return null;
    }
}
