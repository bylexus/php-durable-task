<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;

final class ServiceAndLoggerInjectedStepFixture extends Step
{
    public function __construct(
        private ConstructorInjectedServiceFixture $service,
        private LoggerInterface $injectedLogger,
    ) {
        parent::__construct($injectedLogger);
    }

    public function execute(Task $task): StepResult {
        $task->setPayload('stepService', $this->service->getName());
        $task->setPayload('loggerClass', $this->injectedLogger::class);

        return StepResult::succeeded([
            'injectedStepService' => $this->service->getName(),
            'loggerClass' => $this->injectedLogger::class,
        ]);
    }

    public function getInjectedLogger(): LoggerInterface {
        return $this->injectedLogger;
    }
}
