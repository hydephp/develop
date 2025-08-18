<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestCase;
use Hyde\Support\Facades\Render;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Facades\Blade;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Views\Components\LinkComponent::class)]
class LinkComponentViewTest extends TestCase
{
    public function testLinkComponentCanBeRendered()
    {
        $this->assertSame(
            '<a href="foo">bar</a>',
            rtrim(Blade::render('<x-link href="foo">bar</x-link>'))
        );
    }

    public function testLinkComponentCanBeRenderedWithRoute()
    {
        $route = Routes::get('index');

        $this->assertSame(
            '<a href="index.html">bar</a>',
            rtrim(Blade::render('<x-link href="'.$route.'">bar</x-link>'))
        );
    }

    public function testLinkComponentCanBeRenderedWithRouteForNestedPages()
    {
        Render::share('routeKey', 'foo/bar');
        $route = Routes::get('index');

        $this->assertSame(
            '<a href="../index.html">bar</a>',
            rtrim(Blade::render('<x-link href="'.$route.'">bar</x-link>'))
        );
    }
}
