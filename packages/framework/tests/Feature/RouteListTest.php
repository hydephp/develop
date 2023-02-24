<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
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
        $this->assertSame([
            [
                'Page Type' => 'BladePage',
                'Source File' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/404.blade.php>_pages/404.blade.php</>',
                'Output File' => '_site/404.html',
                'Route Key' => '404',
            ],
            [
                'Page Type' => 'BladePage',
                'Source File' => '<href=file://'.str_replace('\\', '/', Hyde::path()).'/_pages/index.blade.php>_pages/index.blade.php</>',
                'Output File' => '_site/index.html',
                'Route Key' => 'index',
            ],
        ], (new RouteList())->runningInConsole()->toArray());
    }
}
