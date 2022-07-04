<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\Router;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Routing\Router
 */
class RouterTest extends TestCase
{
    /**
     * Test route autodiscovery.
     *
     * @covers \Hyde\Framework\Modules\Routing\Router::__construct
     * @covers \Hyde\Framework\Modules\Routing\Router::getRoutes
     */
    public function test_get_routes_returns_discovered_routes()
    {
        $routes = (new Router())->getRoutes();

        $this->assertContainsOnlyInstancesOf(RouteContract::class, $routes);

        $this->assertEquals(collect([
            '404' => new Route(BladePage::parse('404')),
            'index' => new Route(BladePage::parse('index')),
        ]), $routes);
    }

}
