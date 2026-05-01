<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Result;

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Exception\ConfigurationException;

final class StepResult {
    private StepStatus $status;
    private mixed $payload;
    private ?ErrorInfo $errorInfo;
    /** @var array<string, mixed> */
    private array $meta;
    private ?string $message;

    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        StepStatus $status,
        mixed $payload,
        ?ErrorInfo $errorInfo = null,
        array $meta = [],
        ?string $message = null,
    ) {
        if ($status === StepStatus::QUEUED || $status === StepStatus::RUNNING) {
            throw new ConfigurationException('StepResult status must be succeeded, failed, or cancelled.');
        }

        $this->status = $status;
        $this->payload = $payload;
        $this->errorInfo = $errorInfo;
        $this->meta = $meta;
        $this->message = $message;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function succeeded(mixed $payload, array $meta = [], ?string $message = null): self {
        return new self(StepStatus::SUCCEEDED, $payload, null, $meta, $message);
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function failed(
        mixed $payload,
        ?ErrorInfo $errorInfo = null,
        array $meta = [],
        ?string $message = null,
    ): self {
        return new self(StepStatus::FAILED, $payload, $errorInfo, $meta, $message);
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function cancelled(
        mixed $payload,
        ?ErrorInfo $errorInfo = null,
        array $meta = [],
        ?string $message = null,
    ): self {
        return new self(StepStatus::CANCELLED, $payload, $errorInfo, $meta, $message);
    }

    public function getStatus(): StepStatus {
        return $this->status;
    }

    public function getPayload(): mixed {
        return $this->payload;
    }

    public function getErrorInfo(): ?ErrorInfo {
        return $this->errorInfo;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array {
        return $this->meta;
    }

    public function getMessage(): ?string {
        return $this->message;
    }
}
