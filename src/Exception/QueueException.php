<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Exception;

/**
 * Signals queue operation failures.
 *
 * Is thrown when the queue cannot persist or retrieve workflow records.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
class QueueException extends TaskException {
}
