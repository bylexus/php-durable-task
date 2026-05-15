<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Examples\framework_integration;

use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;

final class PersistUserProfileStep extends Step {
    public function __construct(
        private ExampleUserRepository $repository,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(logger: $logger);
    }

    public function execute(Task $task): StepResult {
        try {
            // This step reads the payload fragment written by FetchUserProfileStep.
            $profile = (array) ($task->getPayload(FetchUserProfileStep::class)->profile ?? []);
            $this->repository->save($profile);

            return StepResult::succeeded(message: 'Profile persisted to repository.');
        } catch (\Throwable $throwable) {
            return StepResult::failed(
                new ErrorInfo((int) $throwable->getCode(), $throwable->getMessage()),
                message: $throwable->getMessage(),
            );
        }
    }
}
