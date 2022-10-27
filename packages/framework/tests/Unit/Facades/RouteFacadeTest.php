<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Route;
use Hyde\Routing\Router;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Route
 */
class RouteFacadeTest extends TestCase
{
    public function test_route_facade_returns_the_router()
    {
        $this->assertInstanceOf(Router::class, Route::getFacadeRoot());
    }

    public function test_route_facade_can_call_methods_on_the_router()
    {
        $this->assertEquals(Router::all(), Route::all());
    }
}
