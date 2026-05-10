<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Enum;

enum RetryMode: string {
    case FAIL = 'fail';
    case RESTART = 'restart';
    case SKIP = 'skip';
}
