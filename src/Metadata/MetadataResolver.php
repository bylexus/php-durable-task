<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Metadata;

use ByLexus\DurableTask\Attribute\CleanupAfter;
use ByLexus\DurableTask\Attribute\MaxRuntime;
use ByLexus\DurableTask\Attribute\Retries;
use ByLexus\DurableTask\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\DurableTask\Enum\RetryMode;
use ByLexus\DurableTask\Exception\ConfigurationException;

final class MetadataResolver {
    /** @var array<class-string, TaskMetadata> */
    private array $taskCache = [];

    /**
     * @var array<class-string, array{retryMode: ?RetryMode, retries: ?int, maxRuntime: ?\DateInterval}>
     */
    private array $stepAttributeCache = [];

    public function resolveTaskMetadata(string $taskClass): TaskMetadata {
        if (isset($this->taskCache[$taskClass])) {
            return $this->taskCache[$taskClass];
        }

        $reflection = $this->reflectClass($taskClass);
        $defaultRetryMode = new RetryModeAttribute(RetryModeAttribute::DEFAULT_MODE);
        $defaultRetries = new Retries(Retries::DEFAULT_COUNT);
        $defaultMaxRuntime = new MaxRuntime(new \DateInterval(MaxRuntime::DEFAULT_SPEC));
        $defaultCleanupAfter = new CleanupAfter(new \DateInterval(CleanupAfter::DEFAULT_SPEC));

        $retryMode = $this->readRetryMode($reflection) ?? $defaultRetryMode->mode;
        $retries = $this->readRetries($reflection) ?? $defaultRetries->count;
        $maxRuntime = $this->readMaxRuntime($reflection) ?? clone $defaultMaxRuntime->interval;
        $cleanupAfter = $this->readCleanupAfter($reflection) ?? clone $defaultCleanupAfter->interval;

        $metadata = new TaskMetadata($retryMode, $retries, $maxRuntime, $cleanupAfter);
        $this->taskCache[$taskClass] = $metadata;

        return $metadata;
    }

    public function resolveStepMetadata(string $stepClass, ?TaskMetadata $taskMetadata = null): StepMetadata {
        $taskMetadata ??= $this->createDefaultTaskMetadata();

        $attributeValues = $this->resolveStepAttributeValues($stepClass);

        return new StepMetadata(
            $attributeValues['retryMode'] ?? $taskMetadata->getRetryMode(),
            $attributeValues['retries'] ?? $taskMetadata->getRetries(),
            $attributeValues['maxRuntime'] ?? $taskMetadata->getMaxRuntime(),
        );
    }

    /**
     * @return array{retryMode: ?RetryMode, retries: ?int, maxRuntime: ?\DateInterval}
     */
    private function resolveStepAttributeValues(string $stepClass): array {
        if (isset($this->stepAttributeCache[$stepClass])) {
            return $this->stepAttributeCache[$stepClass];
        }

        $reflection = $this->reflectClass($stepClass);

        if ($reflection->getAttributes(CleanupAfter::class) !== []) {
            throw new ConfigurationException(sprintf('CleanupAfter is only allowed on task classes: %s', $stepClass));
        }

        $attributeValues = [
            'retryMode' => $this->readRetryMode($reflection),
            'retries' => $this->readRetries($reflection),
            'maxRuntime' => $this->readMaxRuntime($reflection),
        ];

        $this->stepAttributeCache[$stepClass] = $attributeValues;

        return $attributeValues;
    }

    private function reflectClass(string $className): \ReflectionClass {
        if (!class_exists($className)) {
            throw new ConfigurationException(sprintf('Configured class does not exist: %s', $className));
        }

        return new \ReflectionClass($className);
    }

    private function readRetryMode(\ReflectionClass $reflection): ?RetryMode {
        $attributes = $reflection->getAttributes(RetryModeAttribute::class);

        if ($attributes === []) {
            return null;
        }

        /** @var RetryModeAttribute $attribute */
        $attribute = $attributes[0]->newInstance();

        return $attribute->mode;
    }

    private function readRetries(\ReflectionClass $reflection): ?int {
        $attributes = $reflection->getAttributes(Retries::class);

        if ($attributes === []) {
            return null;
        }

        /** @var Retries $attribute */
        $attribute = $attributes[0]->newInstance();

        if ($attribute->count < 0) {
            throw new ConfigurationException(
                sprintf('Retries must not be negative on class %s', $reflection->getName()),
            );
        }

        return $attribute->count;
    }

    private function readMaxRuntime(\ReflectionClass $reflection): ?\DateInterval {
        $attributes = $reflection->getAttributes(MaxRuntime::class);

        if ($attributes === []) {
            return null;
        }

        /** @var MaxRuntime $attribute */
        $attribute = $attributes[0]->newInstance();

        $this->assertPositiveInterval(
            $attribute->interval,
            sprintf('MaxRuntime must be greater than zero on class %s', $reflection->getName()),
        );

        return clone $attribute->interval;
    }

    private function readCleanupAfter(\ReflectionClass $reflection): ?\DateInterval {
        $attributes = $reflection->getAttributes(CleanupAfter::class);

        if ($attributes === []) {
            return null;
        }

        /** @var CleanupAfter $attribute */
        $attribute = $attributes[0]->newInstance();

        $this->assertPositiveInterval(
            $attribute->interval,
            sprintf('CleanupAfter must be greater than zero on class %s', $reflection->getName()),
        );

        return clone $attribute->interval;
    }

    private function createDefaultTaskMetadata(): TaskMetadata {
        $defaultRetryMode = new RetryModeAttribute(RetryModeAttribute::DEFAULT_MODE);
        $defaultRetries = new Retries(Retries::DEFAULT_COUNT);
        $defaultMaxRuntime = new MaxRuntime(new \DateInterval(MaxRuntime::DEFAULT_SPEC));
        $defaultCleanupAfter = new CleanupAfter(new \DateInterval(CleanupAfter::DEFAULT_SPEC));

        return new TaskMetadata(
            $defaultRetryMode->mode,
            $defaultRetries->count,
            $defaultMaxRuntime->interval,
            $defaultCleanupAfter->interval,
        );
    }

    private function assertPositiveInterval(\DateInterval $interval, string $message): void {
        $origin = new \DateTimeImmutable('2000-01-01T00:00:00+00:00');
        $target = $origin->add($interval);

        if ($target <= $origin) {
            throw new ConfigurationException($message);
        }
    }
}
