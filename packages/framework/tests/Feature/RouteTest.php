<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\Route
 */
class RouteTest extends TestCase
{
    public function test_constructor_creates_route_from_page_model()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(Route::class, $route);
    }

    public function test_get_page_type_returns_fully_qualified_class_name()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals(MarkdownPage::class, $route->getPageClass());
    }

    public function test_get_source_model_returns_page_model()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(MarkdownPage::class, $route->getPage());
        $this->assertSame($page, $route->getPage());
    }

    public function test_get_route_key_returns_page_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getRouteKey(), $route->getRouteKey());
    }

    public function test_get_source_file_path_returns_page_source_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getSourcePath(), $route->getSourcePath());
    }

    public function test_get_output_file_path_returns_page_output_path()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getOutputPath(), $route->getOutputPath());
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
        $this->assertEquals(new Route(BladePage::parse('index')), Route::get('index'));
        $this->assertInstanceOf(Route::class, Route::get('index'));
    }

    public function test_get_from_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::get('not-found'));
    }

    public function test_get_supports_dot_notation()
    {
        $this->file('_posts/foo.md');
        $this->assertSame(Route::get('posts/foo'), Route::get('posts.foo'));
    }

    public function test_route_facade_all_method_returns_all_routes()
    {
        $this->assertEquals(Hyde::routes(), Route::all());
    }

    public function test_get_link_returns_correct_path_for_root_pages()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo.html', $route->getLink());
    }

    public function test_get_link_returns_correct_path_for_nested_pages()
    {
        $route = new Route(new MarkdownPage('foo/bar'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo/bar.html', $route->getLink());
    }

    public function test_get_link_returns_correct_path_for_nested_current_page()
    {
        $route = new Route(new MarkdownPage('foo'));
        view()->share('currentPage', 'foo/bar');
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('../foo.html', $route->getLink());
    }

    public function test_get_link_returns_pretty_url_if_enabled()
    {
        config(['site.pretty_urls' => true]);
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo', $route->getLink());
    }

    public function test_to_string_is_alias_for_get_link()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals($route->getLink(), (string) $route);
    }

    public function test_current_returns_current_route()
    {
        $route = new Route(new MarkdownPage('foo'));
        view()->share('currentRoute', $route);
        $this->assertEquals($route, Route::current());
    }

    public function test_current_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::current());
    }

    public function test_to_array_method()
    {
        $this->assertEquals([
            'routeKey' => 'foo',
            'sourcePath' => '_pages/foo.md',
            'outputPath' => 'foo.html',
            'page' => [
                'class' => MarkdownPage::class,
                'identifier' => 'foo',
            ],
        ], (new MarkdownPage('foo'))->getRoute()->toArray());
    }

    public function testIsWithRoute()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is($route));

        $route2 = new Route(new MarkdownPage('bar'));
        $this->assertFalse($route->is($route2));
    }

    public function testIsWithRouteKey()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is('foo'));
        $this->assertFalse($route->is('bar'));
    }

    public function testIsWithRouteKeyObject()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is(new RouteKey('foo')));
        $this->assertFalse($route->is(new RouteKey('bar')));
    }

    public function testExists()
    {
        $this->assertTrue(Route::exists('index'));
        $this->assertFalse(Route::exists('not-found'));
    }
}
