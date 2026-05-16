<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner;

interface DisplayName {
    /**
     * Can be defined in child classes to give the task/step a display name,
     * e.g. when outputting it in a list/console
     *
     * @return string
     */
    public function displayName(): string;
}
