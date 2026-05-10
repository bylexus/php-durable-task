<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Attribute\MaxRuntime;
use ByLexus\TaskRunner\Attribute\Retries;
use ByLexus\TaskRunner\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\TaskRunner\Enum\RetryMode;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/FrameworkDemoContainer.php';

// Step-level attributes override task defaults for retry and runtime behaviour.
#[Retries(5)]
#[RetryModeAttribute(RetryMode::RESTART)]
#[MaxRuntime(new DateInterval('PT30S'))]
final class FetchUserProfileStep extends Step {
    public function __construct(
        private ExampleUserApi $api,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(logger: $logger);
    }

    public function execute(Task $task): StepResult {
        try {
            // The producer stored the user id on the task before enqueueing it.
            $userId = (int) ($task->getPayload()->userId ?? 0);
            $profile = $this->api->fetchById($userId);

            // Persist intermediate data on the task so the next step can consume it.
            $task->getPayload(static::class)->profile = $profile;

            return StepResult::succeeded(message: 'Profile fetched from upstream service.');
        } catch (Throwable $throwable) {
            // Returning a failed result keeps the failure structured in the queue row.
            return StepResult::failed(
                new ErrorInfo((int) $throwable->getCode(), $throwable->getMessage()),
                message: $throwable->getMessage(),
            );
        }
    }
}
