<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Route;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Routing\Route as RouteModel;
use Hyde\Testing\TestCase;
use function unlink;
use function view;

/**
 * @covers \Hyde\Routing\Router
 *
 * @see \Hyde\Framework\Testing\Unit\Facades\RouteFacadeTest
 */
class RouterTest extends TestCase
{
    public function test_get_is_alias_for_get_from_key()
    {
        $this->assertEquals(Route::getFromKey('index'), Route::get('index'));
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

    public function test_get_from_source_returns_route_from_router_index()
    {
        $this->assertEquals(new RouteModel(BladePage::parse('index')), Route::getFromSource('_pages/index.blade.php'));
        $this->assertInstanceOf(RouteModel::class, Route::getFromSource('_pages/index.blade.php'));
    }

    public function test_get_from_source_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::getFromSource('_pages/not-found.blade.php'));
    }

    public function test_get_from_source_can_find_blade_pages()
    {
        Hyde::touch(('_pages/foo.blade.php'));
        $this->assertEquals(new RouteModel(BladePage::parse('foo')), Route::getFromSource('_pages/foo.blade.php'));
        unlink(Hyde::path('_pages/foo.blade.php'));
    }

    public function test_get_from_source_can_find_markdown_pages()
    {
        Hyde::touch(('_pages/foo.md'));
        $this->assertEquals(new RouteModel(MarkdownPage::parse('foo')), Route::getFromSource('_pages/foo.md'));
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_from_source_can_find_markdown_posts()
    {
        Hyde::touch(('_posts/foo.md'));
        $this->assertEquals(new RouteModel(MarkdownPost::parse('foo')), Route::getFromSource('_posts/foo.md'));
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_get_from_source_can_find_documentation_pages()
    {
        Hyde::touch(('_docs/foo.md'));
        $this->assertEquals(new RouteModel(DocumentationPage::parse('foo')), Route::getFromSource('_docs/foo.md'));
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_get_from_model_returns_the_models_route()
    {
        $page = new BladePage('index');
        $this->assertEquals(new RouteModel($page), Route::getFromModel($page));
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

    public function test_current_returns_current_route()
    {
        $route = new RouteModel(new MarkdownPage(identifier: 'foo'));
        view()->share('currentRoute', $route);
        $this->assertEquals($route, Route::current());
    }

    public function test_current_returns_null_if_route_is_not_found()
    {
        $this->assertNull(Route::current());
    }

    public function test_home_helper_returns_index_route()
    {
        $this->assertEquals(Route::get('index'), Route::home());
    }
}
