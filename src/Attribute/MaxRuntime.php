<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class MaxRuntime
{
    public const DEFAULT_SPEC = 'PT1H'; // one hour default runtime per task

    public function __construct(
        public \DateInterval $interval = new \DateInterval(self::DEFAULT_SPEC),
    ) {
    }

    public static function createDefault(): self {
        return new self(new \DateInterval(self::DEFAULT_SPEC));
    }
}
