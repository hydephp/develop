<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\Facades\Routes
 */
class RouteFacadeTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testRouteFacadeAllMethodReturnsAllRoutes()
    {
        $this->assertSame(Hyde::routes(), Routes::all());
    }

    public function testGetThrowsExceptionIfRouteIsNotFound()
    {
        $this->expectException(RouteNotFoundException::class);

        Routes::get('not-found');
    }

    public function testGetReturnsRouteFromRouterIndex()
    {
        $this->assertInstanceOf(Route::class, Routes::get('index'));
    }

    public function testGetReturnsRouteFromRouterIndexForTheRightPage()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Routes::get('index'));
    }

    public function testCurrentReturnsCurrentRoute()
    {
        $route = new Route(new MarkdownPage('foo'));

        self::mockRender()->shouldReceive('getRoute')->andReturn($route);

        $this->assertSame($route, Routes::current());
    }

    public function testCurrentReturnsNullIfRouteIsNotFound()
    {
        self::mockRender()->shouldReceive('getRoute')->andReturn(null);

        $this->assertNull(Routes::current());
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
