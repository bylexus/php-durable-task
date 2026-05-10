<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\FileAttachment;
use PHPUnit\Framework\TestCase;

final class FileAttachmentTest extends TestCase
{
    public function testAttachmentCanRoundtripBetweenFiles(): void {
        $sourcePath = tempnam(sys_get_temp_dir(), 'durable-attachment-source-');
        $targetPath = tempnam(sys_get_temp_dir(), 'durable-attachment-target-');

        self::assertIsString($sourcePath);
        self::assertIsString($targetPath);

        try {
            file_put_contents($sourcePath, "hello attachment\nsecond line");

            $attachment = FileAttachment::fromFile($sourcePath);
            $attachment->toFile($targetPath);

            self::assertSame(basename($sourcePath), $attachment->name());
            self::assertSame(strlen("hello attachment\nsecond line"), $attachment->sizeBytes());
            self::assertSame(
                hash('sha256', "hello attachment\nsecond line"),
                $attachment->sha256(),
            );
            self::assertSame("hello attachment\nsecond line", file_get_contents($targetPath));
        } finally {
            @unlink($sourcePath);
            @unlink($targetPath);
        }
    }

    public function testAttachmentCanBeCreatedFromStringContent(): void {
        $targetPath = tempnam(sys_get_temp_dir(), 'durable-attachment-string-');

        self::assertIsString($targetPath);

        try {
            $attachment = FileAttachment::fromString(
                "string attachment\nsecond line",
                'generated.txt',
                'text/plain',
            );
            $attachment->toFile($targetPath);

            self::assertSame('generated.txt', $attachment->name());
            self::assertSame('text/plain', $attachment->mimeType());
            self::assertSame(strlen("string attachment\nsecond line"), $attachment->sizeBytes());
            self::assertSame(
                hash('sha256', "string attachment\nsecond line"),
                $attachment->sha256(),
            );
            self::assertSame("string attachment\nsecond line", file_get_contents($targetPath));
        } finally {
            @unlink($targetPath);
        }
    }
}
