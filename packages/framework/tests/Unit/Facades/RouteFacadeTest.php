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
    public function testFacadeMethodGet()
    {
        $this->markTestIncomplete('TODO: Reimplement the tests');
        // $this->assertSame(Route::get('index'), RouteModel::get('index'));
    }

    public function testFacadeMethodGetOrFail()
    {
        $this->markTestIncomplete('TODO: Reimplement the tests');
        // $this->assertSame(Route::getOrFail('index'), RouteModel::getOrFail('index'));
    }

    public function testFacadeMethodAll()
    {
        $this->markTestIncomplete('TODO: Reimplement the tests');
        // $this->assertSame(Route::all(), RouteModel::all());
    }

    public function testFacadeMethodCurrent()
    {
        $this->markTestIncomplete('TODO: Reimplement the tests');
        // $this->assertSame(Route::current(), RouteModel::current());
    }

    public function testFacadeMethodExists()
    {
        $this->markTestIncomplete('TODO: Reimplement the tests');
        // $this->assertSame(Route::exists('index'), RouteModel::exists('index'));
    }
}
