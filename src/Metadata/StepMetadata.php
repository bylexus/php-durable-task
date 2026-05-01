<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Metadata;

use ByLexus\DurableTask\Enum\RetryMode;

final class StepMetadata
{
    private RetryMode $retryMode;
    private int $retries;
    private \DateInterval $maxRuntime;

    public function __construct(
        RetryMode $retryMode,
        int $retries,
        \DateInterval $maxRuntime,
    ) {
        $this->retryMode = $retryMode;
        $this->retries = $retries;
        $this->maxRuntime = clone $maxRuntime;
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
}
