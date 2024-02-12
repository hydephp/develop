<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * Test the constructor actions and schema constructors for page models.
 *
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Factories\FeaturedImageFactory
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 * @covers \Hyde\Framework\Factories\BlogPostDataFactory
 */
class PageModelConstructorsTest extends TestCase
{
    public function testDynamicDataConstructorCanFindTitleFromFrontMatter()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'My Title']);
        $page = MarkdownPage::parse('foo');
        $this->assertEquals('My Title', $page->title);
    }

    public function testDynamicDataConstructorCanFindTitleFromH1Tag()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar');
        $page = MarkdownPage::parse('foo');

        $this->assertEquals('Foo Bar', $page->title);
    }

    public function testDynamicDataConstructorCanFindTitleFromSlug()
    {
        $this->markdown('_pages/foo-bar.md');
        $page = MarkdownPage::parse('foo-bar');

        $this->assertEquals('Foo Bar', $page->title);
    }

    public function testDocumentationPageParserCanGetGroupFromFrontMatter()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['navigation.group' => 'foo']);

        $page = DocumentationPage::parse('foo');
        $this->assertEquals('foo', $page->navigationMenuGroup());
    }

    public function testDocumentationPageParserCanGetGroupAutomaticallyFromNestedPage()
    {
        mkdir(Hyde::path('_docs/foo'));
        touch(Hyde::path('_docs/foo/bar.md'));

        /** @var \Hyde\Pages\DocumentationPage $page */
        $page = DocumentationPage::parse('foo/bar');
        $this->assertEquals('foo', $page->navigationMenuGroup());

        Filesystem::unlink('_docs/foo/bar.md');
        rmdir(Hyde::path('_docs/foo'));
    }
}
