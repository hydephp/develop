<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel
 */
class HydeFileHelpersTest extends TestCase
{
    public function test_current_page_returns_current_page_view_property()
    {
        Render::share('routeKey', 'foo');
        $this->assertEquals('foo', Hyde::currentPage());
    }

    public function test_current_page_falls_back_to_empty_string_if_current_page_view_property_is_not_set()
    {
        $this->assertEquals('', Hyde::currentPage());
    }

    public function test_current_route_returns_current_route_view_property()
    {
        Render::share('route', Routes::get('index'));
        $this->assertEquals(Routes::get('index'), Hyde::currentRoute());
    }

    public function test_current_route_falls_back_to_null_if_current_route_view_property_is_not_set()
    {
        $this->assertNull(Hyde::currentRoute());
    }
}
