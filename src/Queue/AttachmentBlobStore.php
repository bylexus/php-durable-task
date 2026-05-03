<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Queue;

use ByLexus\DurableTask\Exception\ConfigurationException;
use ByLexus\DurableTask\Exception\SerializationException;
use PDO;

/**
 * Stores attachment binary data in PostgreSQL.
 *
 * Persists and restores the raw bytes referenced by FileAttachment payload metadata.
 *
 * This file is part of bylexus/durable-task
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class AttachmentBlobStore {
    private PDO $connection;
    private QueueConfiguration $configuration;

    public function __construct(
        PDO $connection,
        ?QueueConfiguration $configuration = null,
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration ?? new QueueConfiguration();
    }

    public function store(int $taskId, string $content, int $sizeBytes, string $sha256): int {
        $statement = $this->connection->prepare(
            sprintf(
                'INSERT INTO %s (task_id, content, size_bytes, sha256, created_at)
                 VALUES (:task_id, :content, :size_bytes, :sha256, :created_at)
                 RETURNING blob_id',
                $this->quotedBlobTableName(),
            ),
        );
        $statement->bindValue('task_id', $taskId, PDO::PARAM_INT);
        $statement->bindValue('content', $content, PDO::PARAM_LOB);
        $statement->bindValue('size_bytes', $sizeBytes, PDO::PARAM_INT);
        $statement->bindValue('sha256', $sha256, PDO::PARAM_STR);
        $statement->bindValue('created_at', $this->currentTimestamp()->format('Y-m-d H:i:sP'), PDO::PARAM_STR);
        $statement->execute();

        $blobId = $statement->fetchColumn();

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
            throw new ConfigurationException(sprintf('Attachment blob %d could not be found.', $blobId));
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
        $statement = $this->connection->prepare(
            'SELECT EXISTS (
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = current_schema()
                  AND table_name = :table_name
            )',
        );
        $statement->execute(['table_name' => $this->configuration->getBlobTableName()]);

        return (bool) $statement->fetchColumn();
    }

    private function currentTimestamp(): \DateTimeImmutable {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    private function quotedBlobTableName(): string {
        return $this->quotedIdentifier($this->configuration->getBlobTableName());
    }

    private function quotedIdentifier(string $identifier): string {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
