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
    public function testFacadeMethodGetCallsSameOnModel()
    {
        $this->assertSame(Route::get('index'), RouteModel::get('index'));
    }

    public function testFacadeMethodGetOrFailCallsSameOnModel()
    {
        $this->assertSame(Route::getOrFail('index'), RouteModel::getOrFail('index'));
    }

    public function testFacadeMethodAllCallsSameOnModel()
    {
        $this->assertSame(Route::all(), RouteModel::all());
    }

    public function testFacadeMethodCurrentCallsSameOnModel()
    {
        $this->assertSame(Route::current(), RouteModel::current());
    }

    public function testFacadeMethodExistsCallsSameOnModel()
    {
        $this->assertSame(Route::exists('index'), RouteModel::exists('index'));
    }
}
