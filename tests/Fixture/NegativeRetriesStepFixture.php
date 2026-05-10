<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\Retries;

#[Retries(-1)]
final class NegativeRetriesStepFixture
{
}
