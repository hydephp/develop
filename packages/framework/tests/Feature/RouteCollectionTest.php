<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Support\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Foundation\RouteCollection
 * @covers \Hyde\Framework\Foundation\Concerns\BaseFoundationCollection
 */
class RouteCollectionTest extends TestCase
{
    protected function test_boot_method_discovers_all_pages()
    {
        $collection = RouteCollection::boot(Hyde::getInstance());

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '404' => (new Route(new BladePage('404'))),
            'index' => (new Route(new BladePage('index'))),
        ], $collection->all());
    }

    protected function test_boot_method_discovers_all_page_types()
    {
        $this->withoutDefaultPages();

        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $collection = Hyde::routes();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            'blade' => (new Route(new BladePage('blade'))),
            'markdown' => (new Route(new MarkdownPage('markdown'))),
            'posts/post' => (new Route(new MarkdownPost('post'))),
            'docs/docs' => (new Route(new DocumentationPage('docs'))),
        ], $collection->all());

        $this->restoreDefaultPages();
    }

    protected function test_get_routes_returns_all_routes()
    {
        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $this->assertSame(Hyde::routes(), Hyde::routes()->getRoutes());
    }

    protected function test_get_routes_for_model_returns_collection_of_routes_of_given_class()
    {
        $this->withoutDefaultPages();

        $this->file('_pages/blade.blade.php');
        $this->file('_pages/markdown.md');
        $this->file('_posts/post.md');
        $this->file('_docs/docs.md');

        $collection = Hyde::routes();

        $this->assertCount(4, $collection);
        $this->assertEquals(new Route(new BladePage('blade')), $collection->getRoutes(BladePage::class)->first());
        $this->assertEquals(new Route(new MarkdownPage('markdown')), $collection->getRoutes(MarkdownPage::class)->first());
        $this->assertEquals(new Route(new MarkdownPost('post')), $collection->getRoutes(MarkdownPost::class)->first());
        $this->assertEquals(new Route(new DocumentationPage('docs')), $collection->getRoutes(DocumentationPage::class)->first());

        $this->restoreDefaultPages();
    }

    protected function test_add_route_adds_new_route()
    {
        $collection = Hyde::routes();
        $this->assertCount(2, $collection);
        $collection->addRoute(new Route(new BladePage('new')));
        $this->assertCount(3, $collection);
        $this->assertEquals(new Route(new BladePage('new')), $collection->last());
    }
}
