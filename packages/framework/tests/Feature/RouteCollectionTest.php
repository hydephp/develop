<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Foundation\Kernel\RouteCollection
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 * @covers \Hyde\Foundation\Facades\Routes
 */
class RouteCollectionTest extends TestCase
{
    public function test_boot_method_discovers_all_pages()
    {
        $collection = RouteCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '404' => new Route(new BladePage('404')),
            'index' => new Route(new BladePage('index')),
        ], $collection->all());
    }

    public function test_boot_method_discovers_all_page_types()
    {
        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();

        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_pages/html.html');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $collection = Hyde::routes();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            'blade' => new Route(new BladePage('blade')),
            'markdown' => new Route(new MarkdownPage('markdown')),
            'html' => new Route(new HtmlPage('html')),
            'posts/post' => new Route(new MarkdownPost('post')),
            'docs/docs' => new Route(new DocumentationPage('docs')),
        ], $collection->all());

        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();
    }

    public function test_get_routes_returns_all_routes()
    {
        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_pages/html.html');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $this->assertSame(Hyde::routes(), Routes::getRoutes());
    }

    public function test_get_routes_for_model_returns_collection_of_routes_of_given_class()
    {
        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();

        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_pages/html.html');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $collection = Hyde::routes();

        $this->assertCount(5, $collection);
        $this->assertEquals(new Route(new BladePage('blade')), Routes::getRoutes(BladePage::class)->first());
        $this->assertEquals(new Route(new MarkdownPage('markdown')), Routes::getRoutes(MarkdownPage::class)->first());
        $this->assertEquals(new Route(new MarkdownPost('post')), Routes::getRoutes(MarkdownPost::class)->first());
        $this->assertEquals(new Route(new DocumentationPage('docs')), Routes::getRoutes(DocumentationPage::class)->first());
        $this->assertEquals(new Route(new HtmlPage('html')), Routes::getRoutes(HtmlPage::class)->first());

        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();
    }

    public function test_add_route_adds_new_route()
    {
        $collection = Hyde::routes();
        $this->assertCount(2, $collection);
        $collection->addRoute(new Route(new BladePage('new')));
        $this->assertCount(3, $collection);
        $this->assertEquals(new Route(new BladePage('new')), $collection->last());
    }

    public function test_get_route()
    {
        $this->assertEquals(new Route(new BladePage('index')), Routes::getRoute('index'));
    }

    public function test_get_route_with_non_existing_route()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route [non-existing] not found');
        Routes::getRoute('non-existing');
    }
}
