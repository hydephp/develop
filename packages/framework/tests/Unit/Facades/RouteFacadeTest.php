<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Route;
use Hyde\Support\Models\Route as RouteModel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Route
 */
class RouteFacadeTest extends TestCase
{
    public function test_route_facade_can_call_methods_on_the_route_model()
    {
        $this->assertEquals(Route::all(), RouteModel::all());
    }

    public function testGet()
    {
        $this->assertSame(Route::get('index'), RouteModel::get('index'));
    }

    public function testGetOrFail()
    {
        $this->assertSame(Route::getOrFail('index'), RouteModel::getOrFail('index'));
    }

    public function testAll()
    {
        $this->assertSame(Route::all(), RouteModel::all());
    }

    public function testCurrent()
    {
        $this->assertSame(Route::current(), RouteModel::current());
    }

    public function testExists()
    {
        $this->assertSame(Route::exists('index'), RouteModel::exists('index'));
    }
}
