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
        $this->assertSame(Hyde::routes(), Routes::all());
    }

    public function testGetOrFailThrowsExceptionIfRouteIsNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        Routes::getOrFail('not-found');
    }

    public function testGetReturnsRouteFromRouterIndex()
    {
        $this->assertInstanceOf(RouteModel::class, Routes::get('index'));
    }

    public function testGetReturnsRouteFromRouterIndexForTheRightPage()
    {
        $this->assertEquals(new RouteModel(BladePage::parse('index')), Routes::get('index'));
    }

    public function testGetFromReturnsNullIfRouteIsNotFound()
    {
        $this->assertNull(Routes::get('not-found'));
    }

    public function testGetSupportsDotNotation()
    {
        Hyde::routes()->add(new RouteModel(new MarkdownPost('foo')));
        $this->assertSame(Routes::get('posts/foo'), Routes::get('posts.foo'));
    }

    public function testCurrentReturnsCurrentRoute()
    {
        $route = new RouteModel(new MarkdownPage('foo'));
        Render::shouldReceive('getCurrentRoute')->andReturn($route);
        $this->assertSame($route, Hyde::currentRoute());
        Render::swap(new RenderModel());
    }

    public function testCurrentReturnsNullIfRouteIsNotFound()
    {
        Render::shouldReceive('getCurrentRoute')->andReturn(null);
        $this->assertNull(Hyde::currentRoute());
        Render::swap(new RenderModel());
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
