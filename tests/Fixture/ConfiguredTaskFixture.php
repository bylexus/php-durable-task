<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\Attribute\MaxRuntime;
use ByLexus\TaskRunner\Attribute\Retries;
use ByLexus\TaskRunner\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\TaskRunner\Enum\RetryMode;

#[CleanupAfter(new \DateInterval('PT30M'), new \DateInterval('P2D'))]
#[RetryModeAttribute(RetryMode::RESTART)]
#[Retries(5, new \DateInterval('PT2M'))]
#[MaxRuntime(new \DateInterval('PT2H'))]
final class ConfiguredTaskFixture
{
}
