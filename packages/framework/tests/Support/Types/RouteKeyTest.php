<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Support\Types;

use Hyde\Support\Types\RouteKey;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Types\RouteKey
 */
class RouteKeyTest extends TestCase
{
    public function test__construct()
    {
        $this->assertInstanceOf(RouteKey::class, new RouteKey('foo'));
    }

    public function test__toString()
    {
        $this->assertSame('foo', (string) new RouteKey('foo'));
    }
}
