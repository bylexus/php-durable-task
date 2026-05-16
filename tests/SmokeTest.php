<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\Exception\TaskException;
use ByLexus\TaskRunner\Task;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase {
    public function testAutoloadBootstrapsProjectSkeleton(): void {
        self::assertTrue(class_exists(Task::class));
        self::assertTrue(is_subclass_of(TaskException::class, \RuntimeException::class));
    }
}
