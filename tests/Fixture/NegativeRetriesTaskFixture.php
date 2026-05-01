<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Attribute\Retries;

#[Retries(-1)]
final class NegativeRetriesTaskFixture
{
}
