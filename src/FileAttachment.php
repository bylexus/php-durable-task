<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner;

use ByLexus\TaskRunner\Exception\ConfigurationException;
use ByLexus\TaskRunner\Exception\QueueException;
use ByLexus\TaskRunner\Exception\SerializationException;
use ByLexus\TaskRunner\Queue\AttachmentBlobStore;

/**
 * Represents a file attachment stored in task payloads.
 *
 * Keeps attachment metadata in the payload while binary content is either held transiently in memory
 * before persistence or resolved through the attachment blob store after hydration.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class FileAttachment {
    public const TYPE_MARKER_FIELD = '__php_tr_type';
    public const TYPE_MARKER_VALUE = 'file_attachment';

    private function __construct(
        private string $name,
        private ?string $mimeType,
        private int $sizeBytes,
        private string $sha256,
        private ?string $content = null,
        private ?int $blobId = null,
        private ?AttachmentBlobStore $blobStore = null,
    ) {
    }

    public static function fromFile(string $path, ?string $name = null): self {
        if ($path === '') {
            throw new ConfigurationException('Attachment path must not be empty.');
        }

        if (!is_file($path)) {
            throw new ConfigurationException(sprintf('Attachment source file does not exist: %s', $path));
        }

        if (!is_readable($path)) {
            throw new ConfigurationException(sprintf('Attachment source file is not readable: %s', $path));
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new SerializationException(sprintf('Failed to read attachment source file: %s', $path));
        }

        $mimeType = mime_content_type($path);

        return new self(
            $name ?: basename($path),
            $mimeType === false ? null : $mimeType,
            strlen($content),
            hash('sha256', $content),
            $content,
        );
    }

    public static function fromString(string $content, string $name, ?string $mimeType = null): self {
        if ($name === '') {
            throw new ConfigurationException('Attachment name must not be empty.');
        }

        return new self(
            $name,
            $mimeType,
            strlen($content),
            hash('sha256', $content),
            $content,
        );
    }

    public static function fromStoredBlob(
        int $blobId,
        string $name,
        ?string $mimeType,
        int $sizeBytes,
        string $sha256,
        ?AttachmentBlobStore $blobStore = null,
    ): self {
        return new self($name, $mimeType, $sizeBytes, $sha256, null, $blobId, $blobStore);
    }

    public function toFile(string $path): void {
        if ($path === '') {
            throw new ConfigurationException('Attachment target path must not be empty.');
        }

        $directory = dirname($path);

        if ($directory !== '.' && !is_dir($directory)) {
            throw new ConfigurationException(sprintf('Attachment target directory does not exist: %s', $directory));
        }

        $written = file_put_contents($path, $this->contents());

        if ($written === false) {
            throw new SerializationException(sprintf('Failed to write attachment to file: %s', $path));
        }
    }

    public function contents(): string {
        if ($this->content !== null) {
            return $this->content;
        }

        if ($this->blobId === null) {
            throw new QueueException('Attachment content is not available.');
        }

        if ($this->blobStore === null) {
            throw new QueueException('Attachment blob store is not bound.');
        }

        $this->content = $this->blobStore->read($this->blobId);

        return $this->content;
    }

    public function name(): string {
        return $this->name;
    }

    public function mimeType(): ?string {
        return $this->mimeType;
    }

    public function sizeBytes(): int {
        return $this->sizeBytes;
    }

    public function sha256(): string {
        return $this->sha256;
    }

    public function blobId(): ?int {
        return $this->blobId;
    }

    public function hasStoredBlob(): bool {
        return $this->blobId !== null;
    }

    public function bindBlobStore(AttachmentBlobStore $blobStore): void {
        $this->blobStore = $blobStore;
    }

    public function toEnvelope(): \stdClass {
        if ($this->blobId === null) {
            throw new ConfigurationException(
                'Attachment must be stored before it can be serialized into payload metadata.',
            );
        }

        return (object) [
            self::TYPE_MARKER_FIELD => self::TYPE_MARKER_VALUE,
            'blobId' => $this->blobId,
            'name' => $this->name,
            'mimeType' => $this->mimeType,
            'sizeBytes' => $this->sizeBytes,
            'sha256' => $this->sha256,
        ];
    }

    public static function fromEnvelope(object $envelope, ?AttachmentBlobStore $blobStore = null): self {
        if (!self::isEnvelope($envelope)) {
            throw new ConfigurationException('Payload value is not a FileAttachment envelope.');
        }

        return self::fromStoredBlob(
            (int) $envelope->blobId,
            (string) $envelope->name,
            isset($envelope->mimeType) && $envelope->mimeType !== null ? (string) $envelope->mimeType : null,
            (int) $envelope->sizeBytes,
            (string) $envelope->sha256,
            $blobStore,
        );
    }

    public static function isEnvelope(mixed $value): bool {
        if (!$value instanceof \stdClass) {
            return false;
        }

        return (
            ($value->{self::TYPE_MARKER_FIELD} ?? null) === self::TYPE_MARKER_VALUE
            && isset($value->blobId)
            && isset($value->name)
            && isset($value->sizeBytes)
            && isset($value->sha256)
        );
    }
}
