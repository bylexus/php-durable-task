<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Exception;

/**
 * Signals configuration errors.
 *
 * Is thrown when task, step, or framework configuration is invalid or incomplete.
 *
 * This file is part of bylexus/php-tr
 *
 * (c) Alexander Schenkel <info@alexi.ch>
 */
class ConfigurationException extends TaskException
{
}
