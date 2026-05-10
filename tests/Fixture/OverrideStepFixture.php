<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\MaxRuntime;
use ByLexus\TaskRunner\Attribute\Retries;
use ByLexus\TaskRunner\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\TaskRunner\Enum\RetryMode;

#[RetryModeAttribute(RetryMode::SKIP)]
#[Retries(1, new \DateInterval('PT15M'))]
#[MaxRuntime(new \DateInterval('PT30M'))]
final class OverrideStepFixture
{
}
