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
   
	   /** @covers Route::getOrFail */
	   public function test_route_facade_getOrFail_method_calls_getOrFail_method()
	   {
		   $this->assertEquals(BaseRoute::getOrFail('index'), Route::getOrFail('index'));
	   }
   
	   /** @covers Route::getFromSource */
	   public function test_route_facade_getFromSource_method_calls_getFromSource_method()
	   {
		   $this->assertEquals(BaseRoute::getFromSource('_pages/index.blade.php'),
			   Route::getFromSource('_pages/index.blade.php'));
	   }
   
	   /** @covers Route::getFromSourceOrFail */
	   public function test_route_facade_getFromSourceOrFail_method_calls_getFromSourceOrFail_method()
	   {
		   $this->assertEquals(BaseRoute::getFromSourceOrFail('_pages/index.blade.php'),
			   Route::getFromSourceOrFail('_pages/index.blade.php'));
	   }
}
