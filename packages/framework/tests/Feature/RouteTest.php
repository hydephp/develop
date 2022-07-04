<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Pages\DocumentationPage;
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

    public function test_get_from_source_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::getFromSource('_pages/index.blade.php'));
        $this->assertInstanceOf(RouteContract::class, Route::getFromSource('_pages/index.blade.php'));
    }

    public function test_get_from_source_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::getFromSource('not-found'));
    }

    public function test_get_from_source_or_fail_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::getFromSourceOrFail('_pages/index.blade.php'));
        $this->assertInstanceOf(RouteContract::class, Route::getFromSourceOrFail('_pages/index.blade.php'));
    }

    /** @covers \Hyde\Framework\Modules\Routing\RouteNotFoundException */
    public function test_get_from_source_or_fail_throws_exception_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage("Route not found: 'not-found'");
        $this->expectExceptionCode(404);

        Route::getFromSourceOrFail('not-found');
    }

    public function test_get_from_source_or_fail_does_not_return_null_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->assertNotNull(Route::getFromSourceOrFail('not-found'));
    }

    public function test_get_from_source_can_find_blade_pages()
    {
        touch(Hyde::path('_pages/foo.blade.php'));
        $this->assertEquals(new Route(BladePage::parse('foo')), Route::getFromSource('_pages/foo.blade.php'));
        unlink(Hyde::path('_pages/foo.blade.php'));
    }

    public function test_get_from_source_can_find_markdown_pages()
    {
        touch(Hyde::path('_pages/foo.md'));
        $this->assertEquals(new Route(MarkdownPage::parse('foo')), Route::getFromSource('_pages/foo.md'));
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_from_source_can_find_markdown_posts()
    {
        touch(Hyde::path('_posts/foo.md'));
        $this->assertEquals(new Route(MarkdownPost::parse('foo')), Route::getFromSource('_posts/foo.md'));
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_from_source_can_find_documentation_pages()
    {
        touch(Hyde::path('_docs/foo.md'));
        $this->assertEquals(new Route(DocumentationPage::parse('foo')), Route::getFromSource('_docs/foo.md'));
        unlink(Hyde::path('_docs/foo.md'));
    }
}
