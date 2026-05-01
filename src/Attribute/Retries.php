<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Retries {
    public const DEFAULT_COUNT = 3;

    public function __construct(public int $count = self::DEFAULT_COUNT) {
    }

    public static function createDefault(): self {
        return new self(self::DEFAULT_COUNT);
    }
}
