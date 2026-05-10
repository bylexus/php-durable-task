<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Exception;

/**
 * Signals serialization failures.
 *
 * Is thrown when workflow payloads or results cannot be serialized or restored.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
class SerializationException extends TaskException {
}
