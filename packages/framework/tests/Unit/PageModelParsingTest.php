<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\CreatesTemporaryFiles;

/**
 * Test the constructor actions and schema constructors for page models.
 *
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Factories\FeaturedImageFactory
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 * @covers \Hyde\Framework\Factories\BlogPostDataFactory
 */
class PageModelParsingTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }

    public function testDynamicDataConstructorCanFindTitleFromFrontMatter()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'My Title']);
        $page = MarkdownPage::parse('foo');

        $this->assertSame('My Title', $page->title);
    }

    public function testDynamicDataConstructorCanFindTitleFromH1Tag()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar');
        $page = MarkdownPage::parse('foo');

        $this->assertSame('Foo Bar', $page->title);
    }

    public function testDynamicDataConstructorCanFindTitleFromSlug()
    {
        $this->markdown('_pages/foo-bar.md');
        $page = MarkdownPage::parse('foo-bar');

        $this->assertSame('Foo Bar', $page->title);
    }

    public function testDocumentationPageParserCanGetGroupFromFrontMatter()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['navigation.group' => 'foo']);
        $page = DocumentationPage::parse('foo');

        $this->assertSame('foo', $page->navigationMenuGroup());
    }

    public function testDocumentationPageParserCanGetGroupAutomaticallyFromNestedPage()
    {
        $this->directory('_docs/foo');
        $this->file('_docs/foo/bar.md');

        $page = DocumentationPage::parse('foo/bar');
        $this->assertSame('foo', $page->navigationMenuGroup());
    }
}
