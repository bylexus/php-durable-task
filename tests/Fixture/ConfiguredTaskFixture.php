<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\Attribute\MaxRuntime;

#[CleanupAfter(new \DateInterval('PT30M'), new \DateInterval('P2D'))]
#[MaxRuntime(new \DateInterval('PT2H'))]
final class ConfiguredTaskFixture
{
}
