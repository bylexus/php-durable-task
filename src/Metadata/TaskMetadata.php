<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Metadata;

/**
 * Stores task execution metadata.
 *
 * Represents resolved runtime and cleanup settings for a task class.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class TaskMetadata
{
    private \DateInterval $maxRuntime;
    private \DateInterval $successfulCleanupAfter;
    private \DateInterval $unsuccessfulCleanupAfter;

    public function __construct(
        \DateInterval $maxRuntime,
        \DateInterval $successfulCleanupAfter,
        \DateInterval $unsuccessfulCleanupAfter,
    ) {
        $this->maxRuntime = clone $maxRuntime;
        $this->successfulCleanupAfter = clone $successfulCleanupAfter;
        $this->unsuccessfulCleanupAfter = clone $unsuccessfulCleanupAfter;
    }

    public function getMaxRuntime(): \DateInterval {
        return clone $this->maxRuntime;
    }

    public function getSuccessfulCleanupAfter(): \DateInterval {
        return clone $this->successfulCleanupAfter;
    }

    public function getUnsuccessfulCleanupAfter(): \DateInterval {
        return clone $this->unsuccessfulCleanupAfter;
    }
}
