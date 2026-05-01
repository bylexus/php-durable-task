<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Metadata;

use ByLexus\DurableTask\Enum\RetryMode;

final class TaskMetadata
{
    private RetryMode $retryMode;
    private int $retries;
    private \DateInterval $maxRuntime;
    private \DateInterval $cleanupAfter;

    public function __construct(
        RetryMode $retryMode,
        int $retries,
        \DateInterval $maxRuntime,
        \DateInterval $cleanupAfter,
    ) {
        $this->retryMode = $retryMode;
        $this->retries = $retries;
        $this->maxRuntime = clone $maxRuntime;
        $this->cleanupAfter = clone $cleanupAfter;
    }

    public function getRetryMode(): RetryMode {
        return $this->retryMode;
    }

    public function getRetries(): int {
        return $this->retries;
    }

    public function getMaxRuntime(): \DateInterval {
        return clone $this->maxRuntime;
    }

    public function getCleanupAfter(): \DateInterval {
        return clone $this->cleanupAfter;
    }
}
