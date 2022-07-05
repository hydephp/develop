<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Facades\Route;
use Hyde\Framework\Modules\Routing\Route as BaseRoute;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Facades\Route
 */
class RouteFacadeTest extends TestCase
{
    /** @covers Route::get */
    public function test_route_facade_get_method_calls_get_method()
    {
        $this->assertEquals(BaseRoute::get('index'), Route::get('index'));
    }


    /** @covers Route::getFromSource */
    public function test_route_facade_get_from_source_method_calls_get_from_source_method()
    {
        $this->assertEquals(BaseRoute::getFromSource('_pages/index.blade.php'),
               Route::getFromSource('_pages/index.blade.php'));
    }
}
