<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;

final class RunnerExceptionStepFixture extends Step
{
    public function execute(): StepResult {
        throw new \RuntimeException('Step exploded.');
    }
}
