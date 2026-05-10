<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Enum;

enum RunnerMode: string {
    case SINGLE = 'single';
    case LOOP = 'loop';
}
