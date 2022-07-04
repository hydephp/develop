<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\Router;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Modules\Routing\Router
 */
class RouterTest extends TestCase
{
    /**
     * @covers \Hyde\Framework\Modules\Routing\Router::getInstance
     */
    public function test_get_instance_returns_the_router_instance()
    {
        // @todo test the singleton once implemented
        $this->assertInstanceOf(Router::class, Router::getInstance());
    }

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
    
    /**
     * Test route autodiscovery.
     *
     * @covers \Hyde\Framework\Modules\Routing\Router::discover
     * @covers \Hyde\Framework\Modules\Routing\Router::discoverRoutes
     * @covers \Hyde\Framework\Modules\Routing\Router::discoverPageRoutes
     */
    public function test_discover_routes_finds_and_adds_all_pages_to_route_collection()
    {
        backup(Hyde::path('_pages/404.blade.php'));
        backup(Hyde::path('_pages/index.blade.php'));
        unlink(Hyde::path('_pages/404.blade.php'));
        unlink(Hyde::path('_pages/index.blade.php'));

        $this->testRouteModelDiscoveryForPageModel(BladePage::class);
        $this->testRouteModelDiscoveryForPageModel(MarkdownPage::class);
        $this->testRouteModelDiscoveryForPageModel(MarkdownPost::class);
        $this->testRouteModelDiscoveryForPageModel(DocumentationPage::class);

        restore(Hyde::path('_pages/404.blade.php'));
        restore(Hyde::path('_pages/index.blade.php'));
    }

    public function test_routes_are_not_discovered_for_disabled_features()
    {
        config(['hyde.features' => []]);

        touch('_pages/blade.blade.php');
        touch('_pages/markdown.md');
        touch('_posts/post.md');
        touch('_docs/doc.md');

        $this->assertEmpty((new Router())->getRoutes());

        unlink('_pages/blade.blade.php');
        unlink('_pages/markdown.md');
        unlink('_posts/post.md');
        unlink('_docs/doc.md');
    }

    public function test_routes_with_custom_source_directories_are_discovered_properly()
    {
        $this->markTestSkipped('TODO');
    }

    public function test_routes_with_custom_output_paths_are_registered_properly()
    {
        $this->markTestSkipped('TODO');
    }

    protected function testRouteModelDiscoveryForPageModel(string $class)
    {
        /** @var PageContract $class */
        touch(Hyde::path($class::qualifyBasename('foo')));

        $expectedKey = 'foo';
        if ($class === MarkdownPost::class) {
            $expectedKey = 'posts/foo';
        }
        if ($class === DocumentationPage::class) {
            $expectedKey = 'docs/foo';
        }

        $expected = collect([
            $expectedKey => new Route($class::parse('foo')),
        ]);

        $this->assertEquals($expected, (new Router())->getRoutes());
        unlink(Hyde::path($class::qualifyBasename('foo')));
    }

    protected function assertHasRoute(RouteContract $route, Collection $routes)
    {
        $this->assertTrue($routes->has($route->getRouteKey()), "Failed asserting route collection has key {$route->getRouteKey()}");
        $this->assertEquals($route, $routes->get($route->getRouteKey()), "Failed asserting route collection has route {$route->getRouteKey()}");
    }
}
