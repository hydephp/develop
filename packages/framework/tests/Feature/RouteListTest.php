<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

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
        ], (new RouteList())->toArray());
    }
}
