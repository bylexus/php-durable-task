<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class ConstructorInjectedStepFixture extends Step
{
    public function __construct(private ConstructorInjectedServiceFixture $service) {
    }

    public function execute(Task $task): StepResult {
        $task->setPayload('stepService', $this->service->getName());

        return StepResult::succeeded(['injectedStepService' => $this->service->getName()]);
    }
}
