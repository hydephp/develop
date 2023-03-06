<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Route;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Render as RenderModel;
use Hyde\Support\Models\Route as RouteModel;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\Route
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
        $this->assertSame(Hyde::routes(), Route::all());
    }

    public function testGetOrFailThrowsExceptionIfRouteIsNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        Route::getOrFail('not-found');
    }

    public function testGetReturnsRouteFromRouterIndex()
    {
        $this->assertInstanceOf(RouteModel::class, Route::get('index'));
    }

    public function testGetReturnsRouteFromRouterIndexForTheRightPage()
    {
        $this->assertEquals(new RouteModel(BladePage::parse('index')), Route::get('index'));
    }

    public function testGetFromReturnsNullIfRouteIsNotFound()
    {
        $this->assertNull(Route::get('not-found'));
    }

    public function testGetSupportsDotNotation()
    {
        Hyde::routes()->add(new RouteModel(new MarkdownPost('foo')));
        $this->assertSame(Route::get('posts/foo'), Route::get('posts.foo'));
    }

    public function testCurrentReturnsCurrentRoute()
    {
        $route = new RouteModel(new MarkdownPage('foo'));
        Render::shouldReceive('getCurrentRoute')->andReturn($route);
        $this->assertSame($route, Route::current());
        Render::swap(new RenderModel());
    }

    public function testCurrentReturnsNullIfRouteIsNotFound()
    {
        Render::shouldReceive('getCurrentRoute')->andReturn(null);
        $this->assertNull(Route::current());
        Render::swap(new RenderModel());
    }

    public function testExistsForExistingRoute()
    {
        $this->assertTrue(Route::exists('index'));
    }

    public function testExistsForNonExistingRoute()
    {
        $this->assertFalse(Route::exists('not-found'));
    }
}
