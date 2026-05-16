<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Queue;

use ByLexus\TaskRunner\Exception\ConfigurationException;
use ByLexus\TaskRunner\Exception\QueueException;
use ByLexus\TaskRunner\Queue\Db\AbstractDatabasePlatform;
use ByLexus\TaskRunner\Queue\Db\DatabasePlatform;
use ByLexus\TaskRunner\Queue\Db\DatabasePlatformResolver;
use ByLexus\TaskRunner\Exception\SerializationException;
use PDO;

/**
 * Stores attachment binary data in the queue.
 *
 * Persists and restores the raw bytes referenced by FileAttachment payload metadata.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class AttachmentBlobStore {
    private PDO $connection;
    private QueueConfiguration $configuration;
    private AbstractDatabasePlatform $platform;

    public function __construct(
        PDO $connection,
        ?QueueConfiguration $configuration = null,
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration ?? new QueueConfiguration();
        $platform = DatabasePlatformResolver::resolve($this->connection);

        if (!$platform instanceof AbstractDatabasePlatform) {
            throw new ConfigurationException('Unsupported database platform implementation.');
        }

        $this->platform = $platform;
        $this->platform->validateConfiguration($this->configuration);
    }

    public function store(int $taskId, string $content, int $sizeBytes, string $sha256): int {
        $statement = $this->connection->prepare(
            sprintf(
                $this->platform->supportsInsertReturning()
                    ? 'INSERT INTO %s (task_id, content, size_bytes, sha256, created_at)
                 VALUES (:task_id, :content, :size_bytes, :sha256, :created_at)
                 RETURNING blob_id'
                    : 'INSERT INTO %s (task_id, content, size_bytes, sha256, created_at)
                 VALUES (:task_id, :content, :size_bytes, :sha256, :created_at)',
                $this->quotedBlobTableName(),
            ),
        );
        $statement->bindValue('task_id', $taskId, PDO::PARAM_INT);
        $statement->bindValue('content', $content, PDO::PARAM_LOB);
        $statement->bindValue('size_bytes', $sizeBytes, PDO::PARAM_INT);
        $statement->bindValue('sha256', $sha256, PDO::PARAM_STR);
        $statement->bindValue('created_at', $this->platform->formatDateTime($this->currentTimestamp()), PDO::PARAM_STR);
        $statement->execute();

        if ($this->platform->supportsInsertReturning()) {
            $blobId = $statement->fetchColumn();
        } else {
            $blobId = $this->connection->lastInsertId();
        }

        if ($blobId === false) {
            throw new SerializationException('Failed to store attachment blob.');
        }

        return (int) $blobId;
    }

    public function read(int $blobId): string {
        $statement = $this->connection->prepare(
            sprintf('SELECT content FROM %s WHERE blob_id = :blob_id', $this->quotedBlobTableName()),
        );
        $statement->bindValue('blob_id', $blobId, PDO::PARAM_INT);
        $statement->execute();

        $content = $statement->fetchColumn();

        if ($content === false) {
            throw new QueueException(sprintf('Attachment blob %d could not be found.', $blobId));
        }

        if (is_resource($content)) {
            $data = stream_get_contents($content);

            if ($data === false) {
                throw new SerializationException(sprintf('Failed to read attachment blob %d.', $blobId));
            }

            return $data;
        }

        if (!is_string($content)) {
            throw new SerializationException(
                sprintf('Attachment blob %d returned an unexpected content type.', $blobId),
            );
        }

        return $content;
    }

    public function tableExists(): bool {
        return $this->platform->tableExists(
            $this->connection,
            $this->configuration,
            $this->configuration->getBlobTableName(),
        );
    }

    private function currentTimestamp(): \DateTimeImmutable {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    private function quotedBlobTableName(): string {
        return $this->platform->qualifyIdentifier(
            $this->configuration->getSchemaName(),
            $this->configuration->getBlobTableName(),
        );
    }
}
