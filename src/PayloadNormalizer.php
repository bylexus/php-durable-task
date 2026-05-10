<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner;

use ByLexus\TaskRunner\Exception\ConfigurationException;
use ByLexus\TaskRunner\Queue\AttachmentBlobStore;

/**
 * Normalizes task payload values.
 *
 * Converts arrays and objects into consistent stdClass payload structures that can be serialized reliably.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
final class PayloadNormalizer {
    public static function normalizeRoot(mixed $payload): \stdClass {
        if ($payload === null) {
            return new \stdClass();
        }

        if ($payload instanceof \stdClass) {
            return $payload;
        }

        if (is_array($payload)) {
            return self::arrayToObject($payload);
        }

        if (is_object($payload)) {
            return self::arrayToObject(get_object_vars($payload));
        }

        throw new ConfigurationException(
            sprintf(
                'Root payload must be null, an array, or an object. Received %s.',
                get_debug_type($payload),
            ),
        );
    }

    public static function normalizeForStorage(
        mixed $payload,
        AttachmentBlobStore $blobStore,
        int $taskId,
    ): \stdClass {
        $rootPayload = self::normalizeRoot($payload);
        $attachmentMap = new \SplObjectStorage();

        return self::normalizeValueForStorage($rootPayload, $blobStore, $taskId, $attachmentMap);
    }

    public static function hydrateStoredRoot(
        mixed $payload,
        ?AttachmentBlobStore $blobStore = null,
    ): \stdClass {
        $rootPayload = self::normalizeRoot($payload);
        $hydratedPayload = self::hydrateStoredValue($rootPayload, $blobStore);

        if (!$hydratedPayload instanceof \stdClass) {
            return self::normalizeRoot($hydratedPayload);
        }

        return $hydratedPayload;
    }

    private static function normalizeValueForStorage(
        mixed $value,
        AttachmentBlobStore $blobStore,
        int $taskId,
        \SplObjectStorage $attachmentMap,
    ): mixed {
        if ($value instanceof FileAttachment) {
            if ($attachmentMap->offsetExists($value)) {
                return $attachmentMap[$value];
            }

            if (!$value->hasStoredBlob()) {
                $envelope = FileAttachment::fromStoredBlob(
                    $blobStore->store($taskId, $value->contents(), $value->sizeBytes(), $value->sha256()),
                    $value->name(),
                    $value->mimeType(),
                    $value->sizeBytes(),
                    $value->sha256(),
                    $blobStore,
                )->toEnvelope();

                $attachmentMap[$value] = $envelope;

                return $envelope;
            }

            $value->bindBlobStore($blobStore);

            $envelope = $value->toEnvelope();
            $attachmentMap[$value] = $envelope;

            return $envelope;
        }

        if ($value instanceof \stdClass) {
            $normalizedObject = new \stdClass();

            foreach (get_object_vars($value) as $property => $propertyValue) {
                $normalizedObject->{$property} = self::normalizeValueForStorage(
                    $propertyValue,
                    $blobStore,
                    $taskId,
                    $attachmentMap,
                );
            }

            return $normalizedObject;
        }

        if (is_array($value)) {
            $normalizedArray = [];

            foreach ($value as $key => $item) {
                $normalizedArray[$key] = self::normalizeValueForStorage(
                    $item,
                    $blobStore,
                    $taskId,
                    $attachmentMap,
                );
            }

            return $normalizedArray;
        }

        if (is_object($value)) {
            $normalizedObject = new \stdClass();

            foreach (get_object_vars($value) as $property => $propertyValue) {
                $normalizedObject->{$property} = self::normalizeValueForStorage(
                    $propertyValue,
                    $blobStore,
                    $taskId,
                    $attachmentMap,
                );
            }

            return $normalizedObject;
        }

        return $value;
    }

    private static function hydrateStoredValue(mixed $value, ?AttachmentBlobStore $blobStore): mixed {
        if (FileAttachment::isEnvelope($value)) {
            return FileAttachment::fromEnvelope($value, $blobStore);
        }

        if ($value instanceof \stdClass) {
            $hydratedObject = new \stdClass();

            foreach (get_object_vars($value) as $property => $propertyValue) {
                $hydratedObject->{$property} = self::hydrateStoredValue($propertyValue, $blobStore);
            }

            return $hydratedObject;
        }

        if (is_array($value)) {
            $hydratedArray = [];

            foreach ($value as $key => $item) {
                $hydratedArray[$key] = self::hydrateStoredValue($item, $blobStore);
            }

            return $hydratedArray;
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private static function arrayToObject(array $payload): \stdClass {
        $object = new \stdClass();

        foreach ($payload as $key => $value) {
            $object->{(string) $key} = $value;
        }

        return $object;
    }
}
