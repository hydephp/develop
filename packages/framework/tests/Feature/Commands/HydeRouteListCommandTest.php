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
            ->expectsTable(['Route Key', 'Source File', 'Output File', 'Page Type'], [
                [
                    '404',
                    '_pages/404.blade.php',
                    '404.html',
                    'BladePage',
                ],
                [
                    'index',
                    '_pages/index.blade.php',
                    'index.html',
                    'BladePage',
                ]
            ])->assertExitCode(0);
    }
}
