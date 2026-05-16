<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;

final class LoggerInjectedStepFixture implements Step
{
    public function __construct(private LoggerInterface $injectedLogger) {
    }

    public function execute(Task $task): StepResult {
        $task->setPayload('loggerClass', $this->injectedLogger::class);

        return StepResult::succeeded(['loggerClass' => $this->injectedLogger::class]);
    }

    public function getInjectedLogger(): LoggerInterface {
        return $this->injectedLogger;
    }
}
