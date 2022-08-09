<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\PageCollection;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\PageCollection
 */
class PageCollectionTest extends TestCase
{
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = PageCollection::boot();
        $this->assertInstanceOf(PageCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new BladePage('404'),
            '_pages/index.blade.php' => new BladePage('index'),
        ], $collection->toArray());
    }

    public function test_blade_pages_are_discovered()
    {
        $this->file('_pages/foo.blade.php');
        $collection = PageCollection::boot();

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->toArray());
        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
    }

    public function test_markdown_pages_are_discovered()
    {
        $this->file('_pages/foo.md');
        $collection = PageCollection::boot();

        $this->assertArrayHasKey('_pages/foo.md', $collection->toArray());
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
    }

    public function test_markdown_posts_are_discovered()
    {
        $this->file('_posts/foo.md');
        $collection = PageCollection::boot();

        $this->assertArrayHasKey('_posts/foo.md', $collection->toArray());
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
    }

    public function test_documentation_pages_are_discovered()
    {
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot();
        $this->assertArrayHasKey('_docs/foo.md', $collection->toArray());
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));
    }

    public function test_get_page_returns_parsed_page_object_for_given_source_path()
    {
        $this->file('_pages/foo.blade.php');
        $collection = PageCollection::boot();
        $this->assertEquals(new BladePage('foo'), $collection->getPage('_pages/foo.blade.php'));
    }

    public function test_get_pages_returns_collection_of_pages_of_given_class()
    {
        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot();
        $this->assertCount(6, $collection);

        $this->assertContainsOnlyInstancesOf(BladePage::class, $collection->getPages(BladePage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, $collection->getPages(MarkdownPage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection->getPages(MarkdownPost::class));
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, $collection->getPages(DocumentationPage::class));

        $this->assertEquals(new BladePage('404'), $collection->getPages(BladePage::class)->first());
        $this->assertEquals(new MarkdownPage('foo'), $collection->getPages(MarkdownPage::class)->first());
        $this->assertEquals(new MarkdownPost('foo'), $collection->getPages(MarkdownPost::class)->first());
        $this->assertEquals(new DocumentationPage('foo'), $collection->getPages(DocumentationPage::class)->first());
    }

    public function test_get_pages_returns_all_pages_when_not_supplied_with_class_string()
    {
        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot()->getPages();
        $this->assertCount(6, $collection);

        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));
    }
}
