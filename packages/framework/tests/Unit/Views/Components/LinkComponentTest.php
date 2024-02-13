<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views\Components;

use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @covers \Hyde\Framework\Views\Components\LinkComponent
 */
class LinkComponentTest extends TestCase
{
    public function testLinkComponentCanBeRendered()
    {
        $this->assertEquals('<a href="foo">bar</a>', rtrim(Blade::render('<x-link href="foo">bar</x-link>')));
    }

    public function testLinkComponentCanBeRenderedWithRoute()
    {
        $route = Routes::get('index');
        $this->assertEquals('<a href="index.html">bar</a>', rtrim(
            Blade::render('<x-link href="'.$route.'">bar</x-link>')));
    }

    public function testLinkComponentCanBeRenderedWithRouteForNestedPages()
    {
        Render::share('routeKey', 'foo/bar');
        $route = Routes::get('index');
        $this->assertEquals('<a href="../index.html">bar</a>', rtrim(
            Blade::render('<x-link href="'.$route.'">bar</x-link>')));
    }
}
