<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\Retries;
use ByLexus\TaskRunner\Attribute\RetryMode as RetryModeAttribute;
use ByLexus\TaskRunner\Enum\RetryMode;

#[RetryModeAttribute(RetryMode::RESTART)]
#[Retries(5, new \DateInterval('PT2M'))]
final class InvalidTaskRetryFixture
{
}
