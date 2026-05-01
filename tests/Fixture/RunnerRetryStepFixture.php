<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Attribute\Retries;
use ByLexus\DurableTask\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\DurableTask\Enum\RetryMode;
use ByLexus\DurableTask\Result\ErrorInfo;
use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;

#[RetryModeAttribute(RetryMode::RESTART)]
#[Retries(1)]
final class RunnerRetryStepFixture extends Step
{
    public function execute(): StepResult {
        $payload = $this->getPayload();

        if (is_object($payload)) {
            $failuresRemaining = (int) ($payload->failuresRemaining ?? 0);

            if ($failuresRemaining > 0) {
                $payload->failuresRemaining = $failuresRemaining - 1;

                return StepResult::failed(
                    $payload,
                    new ErrorInfo(500, 'Retry requested.'),
                    ['willRetry' => true],
                    'Step failed and will be retried.',
                );
            }

            $payload->completed = true;

            return StepResult::succeeded(
                $payload,
                ['retried' => $this->getStepAttempt() > 0],
                'Step succeeded.',
            );
        }

        $failuresRemaining = (int) ($payload['failuresRemaining'] ?? 0);

        if ($failuresRemaining > 0) {
            $payload['failuresRemaining'] = $failuresRemaining - 1;

            return StepResult::failed(
                $payload,
                new ErrorInfo(500, 'Retry requested.'),
                ['willRetry' => true],
                'Step failed and will be retried.',
            );
        }

        $payload['completed'] = true;

        return StepResult::succeeded(
            $payload,
            ['retried' => $this->getStepAttempt() > 0],
            'Step succeeded.',
        );
    }
}
