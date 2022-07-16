<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use Hyde\Framework\Services\RoutingService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Services\RoutingService
 */
class RoutingServiceTest extends TestCase
{
    /**
     * @covers \Hyde\Framework\Services\RoutingService::getInstance
     */
    public function test_get_instance_returns_the_router_instance()
    {
        $this->assertInstanceOf(RoutingService::class, RoutingService::getInstance());
        $this->assertEquals(RoutingService::getInstance(), RoutingService::getInstance());
    }

    /**
     * Test route autodiscovery.
     *
     * @covers \Hyde\Framework\Services\RoutingService::__construct
     * @covers \Hyde\Framework\Services\RoutingService::getRoutes
     */
    public function test_get_routes_returns_discovered_routes()
    {
        $routes = (new RoutingService())->getRoutes();

        $this->assertContainsOnlyInstancesOf(RouteContract::class, $routes);

        $this->assertEquals(collect([
            '404' => new Route(BladePage::parse('404')),
            'index' => new Route(BladePage::parse('index')),
        ]), $routes);
    }

    /**
     * @covers \Hyde\Framework\Services\RoutingService::getRoutesForModel
     */
    public function test_get_routes_for_model_returns_only_routes_for_the_given_model()
    {
        Hyde::touch(('_pages/foo.md'));

        $routes = (new RoutingService())->getRoutesForModel(MarkdownPage::class);

        $this->assertEquals(collect([
            'foo' => new Route(MarkdownPage::parse('foo')),
        ]), $routes);

        unlink(Hyde::path('_pages/foo.md'));
    }

    /**
     * Test route autodiscovery.
     *
     * @covers \Hyde\Framework\Services\RoutingService::discover
     * @covers \Hyde\Framework\Services\RoutingService::discoverRoutes
     * @covers \Hyde\Framework\Services\RoutingService::discoverPageRoutes
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

        $this->assertEmpty((new RoutingService())->getRoutes());

        unlink('_pages/blade.blade.php');
        unlink('_pages/markdown.md');
        unlink('_posts/post.md');
        unlink('_docs/doc.md');
    }

    public function test_add_route_adds_a_route_to_the_routes_collection()
    {
        $routes = (new RoutingService())->getRoutes();

        $this->assertEquals(collect([
            '404' => new Route(BladePage::parse('404')),
            'index' => new Route(BladePage::parse('index')),
        ]), $routes);

        Hyde::touch('_pages/foo.md');
        (new RoutingService())->addRoute(new Route(MarkdownPage::parse('foo')));
        $routes = (new RoutingService())->getRoutes();

        $this->assertEquals(collect([
            '404' => new Route(BladePage::parse('404')),
            'index' => new Route(BladePage::parse('index')),
            'foo' => new Route(MarkdownPage::parse('foo')),
        ]), $routes);

        Hyde::unlink('_pages/foo.md');
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
        Hyde::touch(($class::qualifyBasename('foo')));

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

        $this->assertEquals($expected, (new RoutingService())->getRoutes());
        unlink(Hyde::path($class::qualifyBasename('foo')));
    }

    protected function assertHasRoute(RouteContract $route, Collection $routes)
    {
        $this->assertTrue($routes->has($route->getRouteKey()), "Failed asserting route collection has key {$route->getRouteKey()}");
        $this->assertEquals($route, $routes->get($route->getRouteKey()), "Failed asserting route collection has route {$route->getRouteKey()}");
    }
}
