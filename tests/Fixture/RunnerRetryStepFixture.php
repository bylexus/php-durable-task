<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\Retries;
use ByLexus\TaskRunner\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\TaskRunner\Enum\RetryMode;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

#[RetryModeAttribute(RetryMode::RESTART)]
#[Retries(1, new \DateInterval('PT0S'))]
final class RunnerRetryStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        $payload = $task->getPayload();
        $failuresRemaining = (int) ($payload->failuresRemaining ?? 0);

        if ($failuresRemaining > 0) {
            $payload->failuresRemaining = $failuresRemaining - 1;

            return StepResult::failed(
                new ErrorInfo(500, 'Retry requested.'),
                ['willRetry' => true],
                'Step failed and will be retried.',
            );
        }

        $payload->completed = true;

        return StepResult::succeeded(
            ['retried' => $this->getStepAttempt() > 0],
            'Step succeeded.',
        );
    }
}
