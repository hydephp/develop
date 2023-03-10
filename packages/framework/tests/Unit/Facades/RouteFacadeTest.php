<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\Facades\Routes
 */
class RouteFacadeTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testRouteFacadeAllMethodReturnsAllRoutes()
    {
        $this->assertSame(Hyde::routes(), Routes::all());
    }

    public function testGetOrFailThrowsExceptionIfRouteIsNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        Routes::getOrFail('not-found');
    }

    public function testGetReturnsRouteFromRouterIndex()
    {
        $this->assertInstanceOf(Route::class, Routes::get('index'));
    }

    public function testGetReturnsRouteFromRouterIndexForTheRightPage()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Routes::get('index'));
    }

    public function testGetFromReturnsNullIfRouteIsNotFound()
    {
        $this->assertNull(Routes::get('not-found'));
    }

    public function testGetSupportsDotNotation()
    {
        Hyde::routes()->add(new Route(new MarkdownPost('foo')));
        $this->assertSame(Routes::get('posts/foo'), Routes::get('posts.foo'));
    }

    public function testCurrentReturnsCurrentRoute()
    {
        $route = new Route(new MarkdownPage('foo'));
        Render::shouldReceive('getRoute')->andReturn($route);
        $this->assertSame($route, Routes::current());
        Render::swap(new RenderData());
    }

    public function testCurrentReturnsNullIfRouteIsNotFound()
    {
        Render::shouldReceive('getRoute')->andReturn(null);
        $this->assertNull(Routes::current());
        Render::swap(new RenderData());
    }

    public function testExistsForExistingRoute()
    {
        $this->assertTrue(Routes::exists('index'));
    }

    public function testExistsForNonExistingRoute()
    {
        $this->assertFalse(Routes::exists('not-found'));
    }
}
