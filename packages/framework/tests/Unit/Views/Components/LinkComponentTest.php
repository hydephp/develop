<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views\Components;

use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

/**
 * @covers \Hyde\Framework\Views\Components\LinkComponent
 */
class LinkComponentTest extends TestCase
{
    public function test_link_component_can_be_rendered()
    {
        $this->assertEquals('<a href="foo">bar</a>', rtrim(Blade::render('<x-link href="foo">bar</x-link>')));
    }

    public function test_link_component_can_be_rendered_with_route()
    {
        $route = \Hyde\Support\Models\Route::get('index');
        $this->assertEquals('<a href="index.html">bar</a>', rtrim(
            Blade::render('<x-link href="'.$route.'">bar</x-link>')));
    }

    public function test_link_component_can_be_rendered_with_route_for_nested_pages()
    {
        View::share('currentPage', 'foo/bar');
        $route = \Hyde\Support\Models\Route::get('index');
        $this->assertEquals('<a href="../index.html">bar</a>', rtrim(
            Blade::render('<x-link href="'.$route.'">bar</x-link>')));
    }
}
