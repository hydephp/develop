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
 */
class RouteListTest extends TestCase
{
    public function testRouteList()
    {
        $this->assertSame([
            [
                'Page Type' => 'BladePage',
                'Source File' => '_pages/404.blade.php',
                'Output File' => '_site/404.html',
                'Route Key' => '404',
            ],
            [
                'Page Type' => 'BladePage',
                'Source File' => '_pages/index.blade.php',
                'Output File' => '_site/index.html',
                'Route Key' => 'index',
            ],
        ], (new RouteList())->toArray());
    }

    public function testConsoleRouteList()
    {
        Hyde::routes()->forget('404');

        $this->assertSame([
            [
                'Page Type' => 'BladePage',
                'Source File' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/index.blade.php>_pages/index.blade.php</>',
                'Output File' => '_site/index.html',
                'Route Key' => 'index',
            ],
        ], (new RouteList(true))->toArray());
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

    public function testConsoleRouteListWithClickableOutputPaths()
    {
        Hyde::routes()->forget('404');
        $this->file('_site/index.html');

        $this->assertSame([
            [
                'Page Type' => 'BladePage',
                'Source File' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/index.blade.php>_pages/index.blade.php</>',
                'Output File' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_site/index.html>_site/index.html</>',
                'Route Key' => 'index',
            ],
        ], (new RouteList(true))->toArray());
    }

    public function testWithDynamicPages()
    {
        Hyde::routes()->forget('404');
        Hyde::routes()->forget('index');
        Hyde::routes()->put('foo', new Route(new InMemoryPage('foo')));

        $this->assertSame([
            [
                'Page Type' => 'InMemoryPage',
                'Source File' => 'dynamic',
                'Output File' => '_site/foo.html',
                'Route Key' => 'foo',
            ],
        ], (new RouteList())->toArray());
    }

    public function testConsoleRouteListWithDynamicPages()
    {
        Hyde::routes()->forget('404');
        Hyde::routes()->forget('index');
        Hyde::routes()->put('foo', new Route(new InMemoryPage('foo')));

        $this->assertSame([
            [
                'Page Type' => 'InMemoryPage',
                'Source File' => '<fg=yellow>dynamic</>',
                'Output File' => '_site/foo.html',
                'Route Key' => 'foo',
            ],
        ], (new RouteList(true))->toArray());
    }
}
