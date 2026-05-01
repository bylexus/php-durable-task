<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Enum;

enum RunnerMode: string {
    case SINGLE = 'single';
    case LOOP = 'loop';
}
