<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Attribute\MaxRuntime;

#[MaxRuntime(new \DateInterval('PT0S'))]
final class ZeroMaxRuntimeTaskFixture
{
}
