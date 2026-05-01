<?php

declare(strict_types=1);

namespace ByLexus\DurableTask\Tests\Fixture;

use ByLexus\DurableTask\Attribute\MaxRuntime;

#[MaxRuntime(new \DateInterval('PT0S'))]
final class ZeroMaxRuntimeTaskFixture
{
}
