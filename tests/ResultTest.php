<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests;

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Exception\ConfigurationException;
use ByLexus\DurableTask\Result\ErrorInfo;
use ByLexus\DurableTask\Result\StepResult;
use PHPUnit\Framework\TestCase;

final class ResultTest extends TestCase
{
    public function testErrorInfoExposesConfiguredFields(): void {
        $errorInfo = new ErrorInfo(42, 'Something failed.', ['traceId' => 'abc-123']);

        self::assertSame(42, $errorInfo->getCode());
        self::assertSame('Something failed.', $errorInfo->getMessage());
        self::assertSame(['traceId' => 'abc-123'], $errorInfo->getDetails());
    }

    public function testSucceededResultStoresPayloadMetaAndMessage(): void {
        $result = StepResult::succeeded(['foo' => 'bar'], ['progress' => 'done'], 'All good');

        self::assertSame(StepStatus::SUCCEEDED, $result->getStatus());
        self::assertSame(['foo' => 'bar'], $result->getPayload());
        self::assertNull($result->getErrorInfo());
        self::assertSame(['progress' => 'done'], $result->getMeta());
        self::assertSame('All good', $result->getMessage());
    }

    public function testFailedResultStoresErrorInfo(): void {
        $errorInfo = new ErrorInfo(500, 'Failure', 'raw exception data');
        $result = StepResult::failed(['foo' => 'bar'], $errorInfo, ['retry' => 1], 'Step failed');

        self::assertSame(StepStatus::FAILED, $result->getStatus());
        self::assertSame($errorInfo, $result->getErrorInfo());
        self::assertSame(['retry' => 1], $result->getMeta());
        self::assertSame('Step failed', $result->getMessage());
    }

    public function testCancelledResultStoresErrorInfo(): void {
        $errorInfo = new ErrorInfo(499, 'Cancelled');
        $result = StepResult::cancelled(['foo' => 'bar'], $errorInfo, ['source' => 'signal'], 'Cancelled by runner');

        self::assertSame(StepStatus::CANCELLED, $result->getStatus());
        self::assertSame($errorInfo, $result->getErrorInfo());
        self::assertSame(['source' => 'signal'], $result->getMeta());
        self::assertSame('Cancelled by runner', $result->getMessage());
    }

    public function testQueuedStatusIsRejectedForStepResult(): void {
        $this->expectException(ConfigurationException::class);

        new StepResult(StepStatus::QUEUED, null);
    }

    public function testRunningStatusIsRejectedForStepResultInV1(): void {
        $this->expectException(ConfigurationException::class);

        new StepResult(StepStatus::RUNNING, null);
    }
}
