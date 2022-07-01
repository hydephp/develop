<?php

namespace Hyde\Framework\Testing\Modules\Router;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Modules\Router\Concerns\RouteContract;
use Hyde\Framework\Modules\Router\Route;
use Hyde\Framework\Modules\Router\RouteNotFoundException;
use Hyde\Framework\Modules\Router\Router;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Modules\Router\Router
 */
class RouterTest extends TestCase
{
    public function testRouterConstructorIsProtected()
    {
        $this->assertTrue(
            (new \ReflectionClass(Router::class))->getConstructor()->isProtected(),
            'Failed asserting that constructor has protected visibility.'
        );
    }

    public function test_get_instance_returns_route_instance()
    {
        $this->assertInstanceOf(
            Router::class,
            Router::getInstance()
        );
    }

    public function test_get_instance_returns_same_instance()
    {
        $this->assertSame(
            Router::getInstance(),
            Router::getInstance()
        );
    }

    public function test_router_registers_default_page_models_as_routable()
    {
        $this->assertEquals([
            BladePage::class => true,
            MarkdownPage::class => true,
            MarkdownPost::class => true,
            DocumentationPage::class => true,
        ],
            Router::getInstance()->getRegisteredRouteModels()
        );
    }

    public function test_custom_routable_models_can_be_added()
    {
        $router = Router::getInstance();
        $router->registerRoutableModel('foo');

        $this->assertArrayHasKey(
            'foo', $router->getRegisteredRouteModels()
        );
    }

    public function test_multiple_routable_models_can_be_added_at_once()
    {
        $router = Router::getInstance();
        $router->registerRoutableModels(['foo', 'bar']);

        $this->assertArrayHasKey(
            'foo', $router->getRegisteredRouteModels()
        );
        $this->assertArrayHasKey(
            'bar', $router->getRegisteredRouteModels()
        );
    }

    public function testGetArrayReturnsArray()
    {
        $this->assertIsArray(
            Router::getInstance()->getArray()
        );
    }

    public function testGetJsonReturnsJson()
    {
        $this->assertIsString(
            Router::getInstance()->getJson()
        );

        $this->assertJson(
            Router::getInstance()->getJson()
        );
    }

    public function testGetRoutesReturnsCollection()
    {
        $this->assertInstanceOf(
            Collection::class,
            Router::getInstance()->getRoutes()
        );
    }

    public function testRoutesAreAutomaticallyDiscovered()
    {
        $this->assertCount(
            2, Router::getInstance()->getRoutes()
        );
    }

    public function testRouteCollectionContainsOnlyRoutes()
    {
        $this->assertContainsOnlyInstancesOf(
            RouteContract::class,
            Router::getInstance()->getRoutes()
        );
    }

    public function testRouteCollectionContainsDefaultPageRoutes()
    {
        $routes = Router::getInstance()->getRoutes();

        $this->assertEquals(
            collect([
                new Route(BladePage::class, '_pages/404.blade.php'),
                new Route(BladePage::class, '_pages/index.blade.php'),
            ]),
            $routes
        );
    }

    public function testBladePagesCanBeDiscovered() {
        $this->markTestSkipped('Todo: Test BladePage::class');
	}
    public function testMarkdownPagesCanBeDiscovered() {
        $this->markTestSkipped('Todo: Test MarkdownPage::class');
	}
    public function testMarkdownPostsCanBeDiscovered() {
        $this->markTestSkipped('Todo: Test MarkdownPost::class');
	}
    public function testDocumentationPagesCanBeDiscovered() {
        $this->markTestSkipped('Todo: Test DocumentationPage::class');
	}

    public function testGetRouteReturnsRoute()
    {
        touch(Hyde::path('_pages/foo.md'));

        $this->assertInstanceOf(
            RouteContract::class,
            Router::getInstance()->getRoute('pages.foo')
        );
    }

    public function testGetRouteThrowsRouteNotFoundExceptionIfRouteCouldNotBeResolved()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage("Route 'foo' not found.");
        $this->expectExceptionCode(404);

        Router::getInstance()->getRoute('foo');
    }
}
