<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Support\Route;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel
 */
class HydeFileHelpersTest extends TestCase
{
    public function test_current_page_returns_current_page_view_property()
    {
        view()->share('currentPage', 'foo');
        $this->assertEquals('foo', Hyde::currentPage());
    }

    public function test_current_page_falls_back_to_empty_string_if_current_page_view_property_is_not_set()
    {
        $this->assertEquals('', Hyde::currentPage());
    }

    public function test_current_route_returns_current_route_view_property()
    {
        view()->share('currentRoute', Route::get('index'));
        $this->assertEquals(Route::get('index'), Hyde::currentRoute());
    }

    public function test_current_route_falls_back_to_null_if_current_route_view_property_is_not_set()
    {
        $this->assertNull(Hyde::currentRoute());
    }
}
