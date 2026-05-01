<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests;

use ByLexus\DurableTask\Enum\RetryMode;
use ByLexus\DurableTask\Enum\RunnerMode;
use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function testRetryModeCasesMatchPlan(): void {
        self::assertSame(['fail', 'restart', 'skip'], array_column(RetryMode::cases(), 'value'));
    }

    public function testRunnerModeCasesMatchPlan(): void {
        self::assertSame(['single', 'loop'], array_column(RunnerMode::cases(), 'value'));
    }

    public function testTaskStatusCasesMatchPlan(): void {
        self::assertSame(
            ['queued', 'running', 'succeeded', 'failed', 'cancelled'],
            array_column(TaskStatus::cases(), 'value'),
        );
    }

    public function testStepStatusCasesMatchPlan(): void {
        self::assertSame(
            ['queued', 'running', 'succeeded', 'failed', 'cancelled'],
            array_column(StepStatus::cases(), 'value'),
        );
    }
}
