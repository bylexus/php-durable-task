<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests;

use ByLexus\TaskRunner\Queue\PostgresQueue;
use ByLexus\TaskRunner\Queue\QueueConfiguration;
use PHPUnit\Framework\TestCase;

final class PostgresQueueTest extends TestCase
{
    public function testNotificationChannelStaysWithinPostgresLimit(): void {
        $queue = new PostgresQueue(
            $this->createStub(\PDO::class),
            new QueueConfiguration(str_repeat('table_name_', 8), str_repeat('schema_name_', 6)),
        );

        self::assertLessThanOrEqual(63, strlen($queue->getNotificationChannel()));
    }

    public function testNotificationChannelRemainsDistinctForLongSimilarConfigurations(): void {
        $firstQueue = new PostgresQueue(
            $this->createStub(\PDO::class),
            new QueueConfiguration(
                'customer_background_jobs_for_important_process_variant_alpha_suffix',
                'customer_installation_with_a_really_long_schema_name_segment',
            ),
        );
        $secondQueue = new PostgresQueue(
            $this->createStub(\PDO::class),
            new QueueConfiguration(
                'customer_background_jobs_for_important_process_variant_beta_suffix',
                'customer_installation_with_a_really_long_schema_name_segment',
            ),
        );

        self::assertLessThanOrEqual(63, strlen($firstQueue->getNotificationChannel()));
        self::assertLessThanOrEqual(63, strlen($secondQueue->getNotificationChannel()));
        self::assertNotSame($firstQueue->getNotificationChannel(), $secondQueue->getNotificationChannel());
    }
}
