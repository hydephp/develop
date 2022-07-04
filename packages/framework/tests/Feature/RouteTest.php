<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\RouteNotFoundException;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Routing\Route
 */
class RouteTest extends TestCase
{
    public function test_constructor_creates_route_from_page_model()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(RouteContract::class, $route);
    }

    public function test_get_page_type_returns_fully_qualified_class_name()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals(MarkdownPage::class, $route->getPageType());
    }

    public function test_get_source_model_returns_page_model()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(MarkdownPage::class, $route->getSourceModel());
        $this->assertSame($page, $route->getSourceModel());
    }

    public function test_get_route_key_returns_page_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getCurrentPagePath(), $route->getRouteKey());
    }

    public function test_get_source_file_path_returns_page_source_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getSourcePath(), $route->getSourceFilePath());
    }

    public function test_get_output_file_path_returns_page_output_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getOutputPath(), $route->getOutputFilePath());
    }

    public function test_get_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::get('index'));
        $this->assertInstanceOf(RouteContract::class, Route::get('index'));
    }

    public function test_get_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::get('not-found'));
    }

    public function test_get_or_fail_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::getOrFail('index'));
        $this->assertInstanceOf(RouteContract::class, Route::getOrFail('index'));
    }

    /** @covers \Hyde\Framework\Modules\Routing\RouteNotFoundException */
    public function test_get_or_fail_throws_exception_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage("Route not found: 'not-found'");
        $this->expectExceptionCode(404);

        Route::getOrFail('not-found');
    }

    public function test_get_or_fail_does_not_return_null_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->assertNotNull(Route::getOrFail('not-found'));
    }
}
