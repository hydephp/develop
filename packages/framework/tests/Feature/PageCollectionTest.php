<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Foundation\Kernel\PageCollection
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 * @covers \Hyde\Foundation\Facades\Pages
 */
class PageCollectionTest extends TestCase
{
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = PageCollection::init(Hyde::getInstance())->boot();
        $this->assertInstanceOf(PageCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new BladePage('404'),
            '_pages/index.blade.php' => new BladePage('index'),
        ], $collection->all());
    }

    public function test_blade_pages_are_discovered()
    {
        $this->file('_pages/foo.blade.php');
        $collection = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->all());
        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
    }

    public function test_markdown_pages_are_discovered()
    {
        $this->file('_pages/foo.md');
        $collection = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.md', $collection->all());
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
    }

    public function test_markdown_posts_are_discovered()
    {
        $this->file('_posts/foo.md');
        $collection = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_posts/foo.md', $collection->all());
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
    }

    public function test_documentation_pages_are_discovered()
    {
        $this->file('_docs/foo.md');
        $collection = PageCollection::init(Hyde::getInstance())->boot();
        $this->assertArrayHasKey('_docs/foo.md', $collection->all());
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));
    }

    public function test_get_page_returns_parsed_page_object_for_given_source_path()
    {
        $this->file('_pages/foo.blade.php');
        $this->assertEquals(new BladePage('foo'), Pages::getPage('_pages/foo.blade.php'));
    }

    public function test_get_pages_returns_collection_of_pages_of_given_class()
    {
        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();

        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $this->file('_pages/foo.html');

        $collection = PageCollection::init(Hyde::getInstance())->boot();
        $this->assertCount(5, $collection);

        $this->assertContainsOnlyInstancesOf(BladePage::class, Pages::getPages(BladePage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, Pages::getPages(MarkdownPage::class));
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, Pages::getPages(MarkdownPost::class));
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, Pages::getPages(DocumentationPage::class));
        $this->assertContainsOnlyInstancesOf(HtmlPage::class, Pages::getPages(HtmlPage::class));

        $this->assertEquals(new BladePage('foo'), Pages::getPages(BladePage::class)->first());
        $this->assertEquals(new MarkdownPage('foo'), Pages::getPages(MarkdownPage::class)->first());
        $this->assertEquals(new MarkdownPost('foo'), Pages::getPages(MarkdownPost::class)->first());
        $this->assertEquals(new DocumentationPage('foo'), Pages::getPages(DocumentationPage::class)->first());
        $this->assertEquals(new HtmlPage('foo'), Pages::getPages(HtmlPage::class)->first());

        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();
    }

    public function test_get_pages_returns_all_pages_when_not_supplied_with_class_string()
    {
        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();

        $this->file('_pages/foo.blade.php');
        $this->file('_pages/foo.md');
        $this->file('_posts/foo.md');
        $this->file('_docs/foo.md');
        $this->file('_pages/foo.html');

        $collection = Pages::getPages();
        $this->assertCount(5, $collection);

        $this->assertEquals(new BladePage('foo'), $collection->get('_pages/foo.blade.php'));
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('_pages/foo.md'));
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('_posts/foo.md'));
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('_docs/foo.md'));
        $this->assertEquals(new HtmlPage('foo'), $collection->get('_pages/foo.html'));

        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();
    }

    public function test_get_pages_returns_empty_collection_when_no_pages_are_discovered()
    {
        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();
        $this->assertEmpty(Pages::getPages());
        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();
    }

    public function test_pages_are_not_discovered_for_disabled_features()
    {
        config(['hyde.features' => []]);

        HydeKernel::setInstance(new HydeKernel(Hyde::path()));

        touch('_pages/blade.blade.php');
        touch('_pages/markdown.md');
        touch('_posts/post.md');
        touch('_docs/doc.md');

        $this->assertEmpty(PageCollection::init(Hyde::getInstance())->boot());

        unlink('_pages/blade.blade.php');
        unlink('_pages/markdown.md');
        unlink('_posts/post.md');
        unlink('_docs/doc.md');
    }

    public function test_pages_with_custom_source_directories_are_discovered_properly()
    {
        $this->withoutDocumentationSearch();

        BladePage::setSourceDirectory('.source/pages');
        MarkdownPage::setSourceDirectory('.source/pages');
        MarkdownPost::setSourceDirectory('.source/posts');
        DocumentationPage::setSourceDirectory('.source/docs');

        $this->directory('.source');
        $this->directory('.source/pages');
        $this->directory('.source/posts');
        $this->directory('.source/docs');

        $this->file('.source/pages/foo.blade.php');
        $this->file('.source/pages/foo.md');
        $this->file('.source/posts/foo.md');
        $this->file('.source/docs/foo.md');

        $collection = Pages::getPages();
        $this->assertCount(4, $collection);

        $this->assertEquals(new BladePage('foo'), $collection->get('.source/pages/foo.blade.php'));
        $this->assertEquals(new MarkdownPage('foo'), $collection->get('.source/pages/foo.md'));
        $this->assertEquals(new MarkdownPost('foo'), $collection->get('.source/posts/foo.md'));
        $this->assertEquals(new DocumentationPage('foo'), $collection->get('.source/docs/foo.md'));

        $this->restoreDocumentationSearch();
    }

    public function test_get_file_throws_exception_when_file_is_not_found()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_pages/foo.blade.php] not found');

        Pages::getPage('_pages/foo.blade.php');
    }
}
