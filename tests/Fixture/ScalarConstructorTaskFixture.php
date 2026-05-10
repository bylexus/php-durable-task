<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class ScalarConstructorTaskFixture extends Task
{
    public function __construct(private string $name) {
    }

    public function nextStep(?Step $actStep = null): ?Step {
        return null;
    }
}
