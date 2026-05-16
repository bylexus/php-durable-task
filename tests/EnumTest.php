<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\Enum\RetryMode;
use ByLexus\TaskRunner\Enum\StepStatus;
use ByLexus\TaskRunner\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function testRetryModeCasesMatchPlan(): void {
        self::assertSame(['fail', 'restart', 'skip'], array_column(RetryMode::cases(), 'value'));
    }

    public function testTaskStatusCasesMatchPlan(): void {
        self::assertSame(
            ['queued', 'running', 'succeeded', 'failed', 'cancelled'],
            array_column(TaskStatus::cases(), 'value'),
        );
    }

    public function testStepStatusCasesMatchPlan(): void {
        self::assertSame(
            ['queued', 'running', 'succeeded', 'failed', 'cancelled', 'skipped'],
            array_column(StepStatus::cases(), 'value'),
        );
    }
}
