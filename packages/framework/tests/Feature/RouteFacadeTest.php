<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Facades\Route as RouteFacade;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Facades\Route
 */
class RouteFacadeTest extends TestCase
{
       /** @covers ::get */
	   public function test_route_facade_get_method_calls_get_method()
	   {
		   $this->assertEquals(Route::get('index'), RouteFacade::get('index'));
	   }
   
	   /** @covers ::getOrFail */
	   public function test_route_facade_getOrFail_method_calls_getOrFail_method()
	   {
		   $this->assertEquals(Route::getOrFail('index'), RouteFacade::getOrFail('index'));
	   }
   
	   /** @covers ::getFromSource */
	   public function test_route_facade_getFromSource_method_calls_getFromSource_method()
	   {
		   $this->assertEquals(Route::getFromSource('_pages/index.blade.php'),
			   RouteFacade::getFromSource('_pages/index.blade.php'));
	   }
   
	   /** @covers ::getFromSourceOrFail */
	   public function test_route_facade_getFromSourceOrFail_method_calls_getFromSourceOrFail_method()
	   {
		   $this->assertEquals(Route::getFromSourceOrFail('_pages/index.blade.php'),
			   RouteFacade::getFromSourceOrFail('_pages/index.blade.php'));
	   }
}
