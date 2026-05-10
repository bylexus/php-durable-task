<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\CleanupAfter;

#[CleanupAfter(new \DateInterval('PT0S'), new \DateInterval('P1D'))]
final class InvalidCleanupOnStepFixture
{
}
