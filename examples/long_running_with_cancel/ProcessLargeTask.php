<?php

declare(strict_types=1);

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/ProcessLargeImportStep.php';

#[CleanupAfter(new DateInterval('PT2H'))]
final class ProcessLargeTask extends Task {
    public function __construct(
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(logger: $logger);
    }

    public function nextStep(?Step $actStep = null): ?Step {
        if ($actStep === null) {
            return new ProcessLargeImportStep($this->getLogger());
        }
        return null;
    }

    public function setAmountOfWork(int $nr): self {
        $this->setPayload('workItems', $nr);
        return $this;
    }

    public function getAmountOfWork(): int {
        return $this->getPayload('workItems') ?: 10;
    }
}
