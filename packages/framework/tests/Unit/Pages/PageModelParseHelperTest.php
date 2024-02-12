<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Facades\Filesystem;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\Concerns\HydePage::parse
 */
class PageModelParseHelperTest extends TestCase
{
    public function testBladePageGetHelperReturnsBladePageObject()
    {
        Filesystem::touch('_pages/foo.blade.php');

        $object = BladePage::parse('foo');
        $this->assertInstanceOf(BladePage::class, $object);

        Filesystem::unlink('_pages/foo.blade.php');
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageObject()
    {
        Filesystem::touch('_pages/foo.md');

        $object = MarkdownPage::parse('foo');
        $this->assertInstanceOf(MarkdownPage::class, $object);

        Filesystem::unlink('_pages/foo.md');
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostObject()
    {
        Filesystem::touch('_posts/foo.md');

        $object = MarkdownPost::parse('foo');
        $this->assertInstanceOf(MarkdownPost::class, $object);

        Filesystem::unlink('_posts/foo.md');
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageObject()
    {
        Filesystem::touch('_docs/foo.md');

        $object = DocumentationPage::parse('foo');
        $this->assertInstanceOf(DocumentationPage::class, $object);

        Filesystem::unlink('_docs/foo.md');
    }
}
