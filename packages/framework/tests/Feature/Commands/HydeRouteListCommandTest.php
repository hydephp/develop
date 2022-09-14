<?php

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
                    '404.html',
                    '404',
                ],
                [
                    'BladePage',
                    '_pages/index.blade.php',
                    'index.html',
                    'index',
                ],
            ])->assertExitCode(0);
    }
}
