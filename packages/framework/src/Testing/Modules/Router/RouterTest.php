<?php

namespace Hyde\Framework\Testing\Modules\Router;

use Hyde\Framework\Modules\Router\Router;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Router\Router
 */
class RouterTest extends TestCase
{
    public function testRouterConstructorIsProtected()
    {
        $this->assertTrue(
            (new \ReflectionClass(Router::class))->getConstructor()->isProtected(),
            'Failed asserting that constructor has protected visibility.'
        );
    }

    public function test_get_instance_returns_route_instance()
    {
        $this->assertInstanceOf(
            Router::class,
            Router::getInstance()
        );
    }

    public function test_get_instance_returns_same_instance()
    {
        $this->assertSame(
            Router::getInstance(),
            Router::getInstance()
        );
    }
}
