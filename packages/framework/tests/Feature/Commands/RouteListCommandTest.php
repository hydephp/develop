<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\RouteListCommand
 *
 * @see \Hyde\Framework\Testing\Feature\RouteListTest
 */
class RouteListCommandTest extends TestCase
{
    public function testRouteListCommand()
    {
        $this->artisan('route:list')
            ->expectsTable(['Page Type', 'Source File', 'Output File', 'Route Key'], [
                [
                    'BladePage',
                    '_pages/404.blade.php',
                    '_site/404.html',
                    '404',
                ],
                [
                    'BladePage',
                    '_pages/index.blade.php',
                    '_site/index.html',
                    'index',
                ],
            ])->assertExitCode(0);
    }

    public function testConsoleRouteList()
    {
        Hyde::routes()->forget('404');

        $this->artisan('route:list')
            ->expectsTable(['Page Type', 'Source File', 'Output File', 'Route Key'], [[
                'page_type' => 'BladePage',
                'source_file' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/index.blade.php>_pages/index.blade.php</>',
                'output_file' => '_site/index.html',
                'route_key' => 'index',
            ]])->assertExitCode(0);
    }

    public function testConsoleRouteListWithClickableOutputPaths()
    {
        Hyde::routes()->forget('404');
        $this->file('_site/index.html');

        $this->artisan('route:list')
            ->expectsTable(['Page Type', 'Source File', 'Output File', 'Route Key'], [[
                'page_type' => 'BladePage',
                'source_file' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/index.blade.php>_pages/index.blade.php</>',
                'output_file' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_site/index.html>_site/index.html</>',
                'route_key' => 'index',
            ]])->assertExitCode(0);
    }

    public function testConsoleRouteListWithDynamicPages()
    {
        Hyde::routes()->forget('404');
        Hyde::routes()->forget('index');
        Hyde::routes()->put('foo', new Route(new InMemoryPage('foo')));

        $this->artisan('route:list')
            ->expectsTable(['Page Type', 'Source File', 'Output File', 'Route Key'], [[
                'page_type' => 'InMemoryPage',
                'source_file' => '<fg=yellow>dynamic</>',
                'output_file' => '_site/foo.html',
                'route_key' => 'foo',
            ]])->assertExitCode(0);
    }
}
