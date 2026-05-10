<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class ConstructorInjectedTaskFixture extends Task
{
    public function __construct(private ConstructorInjectedServiceFixture $service) {
    }

    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new ConstructorInjectedStepFixture($this->service);
        }

        return null;
    }

    public function updateStep(Step $step, StepResult $result): void {
        parent::updateStep($step, $result);
        $this->setPayload('taskService', $this->service->getName());
    }

    public function getInjectedServiceName(): string {
        return $this->service->getName();
    }
}
