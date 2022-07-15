<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers Hyde::currentPage
 */
class HydeCurrentPageTest extends TestCase
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
}