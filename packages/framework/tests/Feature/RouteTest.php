<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\Router;
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

    public function test_get_is_alias_for_get_from_key()
    {
        $this->assertEquals(Route::getFromKey('index'), Route::get('index'));
    }

    public function test_get_from_key_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::get('index'));
        $this->assertInstanceOf(RouteContract::class, Route::get('index'));
    }

    public function test_get_from_key_throws_exception_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        Route::get('not-found');
    }

    public function test_get_from_source_returns_route_from_router_index()
    {
        $this->assertEquals(new Route(BladePage::parse('index')), Route::getFromSource('_pages/index.blade.php'));
        $this->assertInstanceOf(RouteContract::class, Route::getFromSource('_pages/index.blade.php'));
    }

    public function test_get_from_source_returns_null_if_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);
        Route::getFromSource('not-found');
    }

    public function test_get_from_source_can_find_blade_pages()
    {
        Hyde::touch(('_pages/foo.blade.php'));
        $this->assertEquals(new Route(BladePage::parse('foo')), Route::getFromSource('_pages/foo.blade.php'));
        unlink(Hyde::path('_pages/foo.blade.php'));
    }

    public function test_get_from_source_can_find_markdown_pages()
    {
        Hyde::touch(('_pages/foo.md'));
        $this->assertEquals(new Route(MarkdownPage::parse('foo')), Route::getFromSource('_pages/foo.md'));
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_from_source_can_find_markdown_posts()
    {
        Hyde::touch(('_posts/foo.md'));
        $this->assertEquals(new Route(MarkdownPost::parse('foo')), Route::getFromSource('_posts/foo.md'));
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_from_source_can_find_documentation_pages()
    {
        Hyde::touch(('_docs/foo.md'));
        $this->assertEquals(new Route(DocumentationPage::parse('foo')), Route::getFromSource('_docs/foo.md'));
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_get_from_model_returns_the_models_route()
    {
        $page = new BladePage('index');
        $this->assertEquals(new Route($page), Route::getFromModel($page));
    }

    public function test_route_facade_all_method_returns_all_routes()
    {
        $this->assertEquals(Router::getInstance()->getRoutes(), Route::all());
    }

    // test getLink uses hyde::relativeLink() to get the correct path for root pages
    public function test_get_link_returns_correct_path_for_root_pages()
    {
        $route = new Route(new MarkdownPage(slug: 'foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputFilePath()), $route->getLink());
        $this->assertEquals('foo.html', $route->getLink());
    }

    // test getLink uses hyde::relativeLink() to get the correct path for nested pages
    public function test_get_link_returns_correct_path_for_nested_pages()
    {
        $route = new Route(new MarkdownPage(slug: 'foo/bar'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputFilePath()), $route->getLink());
        $this->assertEquals('foo/bar.html', $route->getLink());
    }

    public function test_get_link_returns_correct_path_for_nested_current_page()
    {
        $route = new Route(new MarkdownPage(slug: 'foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputFilePath(), 'foo/bar'), $route->getLink('foo/bar'));
        $this->assertEquals('../foo.html', $route->getLink('foo/bar'));
    }

    // test getLink returns pretty url if enabled
    public function test_get_link_returns_pretty_url_if_enabled()
    {
        config(['hyde.pretty_urls' => true]);
        $route = new Route(new MarkdownPage(slug: 'foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputFilePath()), $route->getLink());
        $this->assertEquals('foo', $route->getLink());
    }
}
