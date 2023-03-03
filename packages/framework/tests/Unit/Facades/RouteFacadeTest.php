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

    public function test_route_facade_all_method_returns_all_routes()
    {
        $this->assertEquals(Hyde::routes(), Route::all());
    }

    public function test_get_is_alias_for_get_from_key()
    {
        $this->assertEquals(Route::get('index'), Route::get('index'));
    }

    public function test_get_or_fail_throws_exception_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        Route::getOrFail('not-found');
    }

    public function test_get_from_key_returns_route_from_router_index()
    {
        $this->assertEquals(new RouteModel(BladePage::parse('index')), Route::get('index'));
        $this->assertInstanceOf(RouteModel::class, Route::get('index'));
    }

    public function test_get_from_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::get('not-found'));
    }

    public function test_get_supports_dot_notation()
    {
        Hyde::routes()->add(new RouteModel(new MarkdownPost('foo')));
        $this->assertSame(Route::get('posts/foo'), Route::get('posts.foo'));
    }

    public function test_current_returns_current_route()
    {
        $route = new RouteModel(new MarkdownPage('foo'));
        Render::shouldReceive('getCurrentRoute')->andReturn($route);
        $this->assertEquals($route, Route::current());
        Render::swap(new \Hyde\Support\Models\Render());
    }

    public function test_current_returns_null_if_route_is_not_found()
    {
        Render::shouldReceive('getCurrentRoute')->andReturn(null);
        $this->assertNull(Route::current());
        Render::swap(new \Hyde\Support\Models\Render());
    }

    public function testExists()
    {
        $this->assertTrue(Route::exists('index'));
        $this->assertFalse(Route::exists('not-found'));
    }
}
