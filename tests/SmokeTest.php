<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\Enum\RunnerMode;
use ByLexus\TaskRunner\Exception\TaskException;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase {
    public function testAutoloadBootstrapsProjectSkeleton(): void {
        self::assertTrue(enum_exists(RunnerMode::class));
        self::assertTrue(is_subclass_of(TaskException::class, \RuntimeException::class));
    }
}
