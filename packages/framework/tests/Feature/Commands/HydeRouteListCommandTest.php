<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeRouteListCommand
 */
class HydeRouteListCommandTest extends TestCase
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
}
