<?php

namespace Hyde\Framework\Testing\Modules\Router;

use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
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

}
