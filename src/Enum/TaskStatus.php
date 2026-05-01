<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Enum;

enum TaskStatus: string {
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
