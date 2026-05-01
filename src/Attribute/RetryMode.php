<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Attribute;

use ByLexus\DurableTask\Enum\RetryMode as RetryModeEnum;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class RetryMode
{
    public const DEFAULT_MODE = RetryModeEnum::FAIL;

    public function __construct(
        public RetryModeEnum $mode = self::DEFAULT_MODE,
    ) {
    }

    public static function createDefault(): self {
        return new self(self::DEFAULT_MODE);
    }
}
