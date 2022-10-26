<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Foundation\PageCollection
 * @covers \Hyde\Framework\Foundation\Concerns\BaseFoundationCollection
 */
class PageCollectionTest extends TestCase
{
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = PageCollection::boot(Hyde::getInstance());
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
        $collection = PageCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->toArray());
        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
    }

    public function test_markdown_pages_are_discovered()
    {
        $this->file('_pages/foo.md');
        $collection = PageCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_pages/foo.md', $collection->toArray());
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
    }

    public function test_markdown_posts_are_discovered()
    {
        $this->file('_posts/foo.md');
        $collection = PageCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_posts/foo.md', $collection->toArray());
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
    }

    public function test_documentation_pages_are_discovered()
    {
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot(Hyde::getInstance());
        $this->assertArrayHasKey('_docs/foo.md', $collection->toArray());
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));
    }

    public function test_get_page_returns_parsed_page_object_for_given_source_path()
    {
        $this->file('_pages/foo.blade.php');
        $collection = PageCollection::boot(Hyde::getInstance());
        $this->assertEquals(new BladePage('foo'), $collection->getPage('_pages/foo.blade.php'));
    }

    public function test_get_pages_returns_collection_of_pages_of_given_class()
    {
        $this->withoutDefaultPages();

        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot(Hyde::getInstance());
        $this->assertCount(4, $collection);

        $this->assertContainsOnlyInstancesOf(BladePage::class, $collection->getPages(BladePage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, $collection->getPages(MarkdownPage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection->getPages(MarkdownPost::class));
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, $collection->getPages(DocumentationPage::class));

        $this->assertEquals(new BladePage('foo'), $collection->getPages(BladePage::class)->first());
        $this->assertEquals(new MarkdownPage('foo'), $collection->getPages(MarkdownPage::class)->first());
        $this->assertEquals(new MarkdownPost('foo'), $collection->getPages(MarkdownPost::class)->first());
        $this->assertEquals(new DocumentationPage('foo'), $collection->getPages(DocumentationPage::class)->first());

        $this->restoreDefaultPages();
    }

    public function test_get_pages_returns_all_pages_when_not_supplied_with_class_string()
    {
        $this->withoutDefaultPages();

        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $collection = PageCollection::boot(Hyde::getInstance())->getPages();
        $this->assertCount(4, $collection);

        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));

        $this->restoreDefaultPages();
    }

    public function test_get_pages_returns_empty_collection_when_no_pages_are_discovered()
    {
        $this->withoutDefaultPages();
        $collection = PageCollection::boot(Hyde::getInstance());
        $this->assertEmpty($collection->getPages());
        $this->restoreDefaultPages();
    }

    public function test_pages_are_not_discovered_for_disabled_features()
    {
        config(['hyde.features' => []]);

        touch('_pages/blade.blade.php');
        touch('_pages/markdown.md');
        touch('_posts/post.md');
        touch('_docs/doc.md');

        $this->assertEmpty(PageCollection::boot(Hyde::getInstance()));

        unlink('_pages/blade.blade.php');
        unlink('_pages/markdown.md');
        unlink('_posts/post.md');
        unlink('_docs/doc.md');
    }

    public function test_pages_with_custom_source_directories_are_discovered_properly()
    {
        BladePage::$sourceDirectory = '.source/pages';
        MarkdownPage::$sourceDirectory = '.source/pages';
        MarkdownPost::$sourceDirectory = '.source/posts';
        DocumentationPage::$sourceDirectory = '.source/docs';

        mkdir(Hyde::path('.source'));
        mkdir(Hyde::path('.source/pages'));
        mkdir(Hyde::path('.source/posts'));
        mkdir(Hyde::path('.source/docs'));

        touch(Hyde::path('.source/pages/foo.blade.php'));
        touch(Hyde::path('.source/pages/foo.md'));
        touch(Hyde::path('.source/posts/foo.md'));
        touch(Hyde::path('.source/docs/foo.md'));

        $collection = PageCollection::boot(Hyde::getInstance())->getPages();
        $this->assertCount(4, $collection);

        $this->assertEquals(new BladePage('foo'), $collection->get('.source/pages/foo.blade.php'));
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('.source/pages/foo.md'));
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('.source/posts/foo.md'));
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('.source/docs/foo.md'));

        File::deleteDirectory(Hyde::path('.source'));
    }
}
