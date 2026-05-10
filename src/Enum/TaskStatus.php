<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Enum;

enum TaskStatus: string {
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
