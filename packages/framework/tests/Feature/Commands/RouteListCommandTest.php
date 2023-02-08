<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\RouteListCommand
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

    public function testClickableLinks()
    {
        $this->file('_site/index.html');
        $this->artisan('route:list')
            ->assertExitCode(0);
    }

    public function testWithDynamicPages()
    {
        Hyde::routes()->put('foo', new Route(new InMemoryPage('foo')));

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
                [
                    'InMemoryPage',
                    '<fg=yellow>dynamic</>',
                    '_site/foo.html',
                    'foo',
                ],
            ])->assertExitCode(0);
    }
}
