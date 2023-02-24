<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteList;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\RouteList
 * @covers \Hyde\Support\Models\RouteListItem
 */
class RouteListTest extends TestCase
{
    public function testRouteList()
    {
        $this->assertSame([
            [
                'page_type' => 'BladePage',
                'source_file' => '_pages/404.blade.php',
                'output_file' => '_site/404.html',
                'route_key' => '404',
            ],
            [
                'page_type' => 'BladePage',
                'source_file' => '_pages/index.blade.php',
                'output_file' => '_site/index.html',
                'route_key' => 'index',
            ],
        ], (new RouteList())->toArray());
    }

    public function testHeaders()
    {
        $this->assertSame([
            'Page Type',
            'Source File',
            'Output File',
            'Route Key',
        ], (new RouteList())->headers());
    }

    public function testWithDynamicPages()
    {
        Hyde::routes()->forget('404');
        Hyde::routes()->forget('index');
        Hyde::routes()->put('foo', new Route(new InMemoryPage('foo')));

        $this->assertSame([
            [
                'page_type' => 'InMemoryPage',
                'source_file' => 'none',
                'output_file' => '_site/foo.html',
                'route_key' => 'foo',
            ],
        ], (new RouteList())->toArray());
    }
}
