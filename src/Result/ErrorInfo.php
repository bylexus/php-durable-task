<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Result;

final class ErrorInfo {
    private int $code;
    private string $message;
    private mixed $details;

    public function __construct(
        int $code,
        string $message,
        mixed $details = null,
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->details = $details;
    }

    public function getCode(): int {
        return $this->code;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getDetails(): mixed {
        return $this->details;
    }
}
