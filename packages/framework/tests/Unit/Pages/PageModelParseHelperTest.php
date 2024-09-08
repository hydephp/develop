<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Pages\Concerns\HydePage::parse
 */
class PageModelParseHelperTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testBladePageGetHelperReturnsBladePageObject()
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->with(Hyde::path('_pages/foo.blade.php'))->andReturn(false)
            ->shouldReceive('isFile')->with(Hyde::path('_pages/foo.blade.php'))->andReturn(true)
            ->shouldReceive('get')->with(Hyde::path('_pages/foo.blade.php'))->andReturn('foo');

        $object = BladePage::parse('foo');
        $this->assertInstanceOf(BladePage::class, $object);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageObject()
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->with(Hyde::path('_pages/foo.md'))->andReturn(false)
            ->shouldReceive('isFile')->with(Hyde::path('_pages/foo.md'))->andReturn(true)
            ->shouldReceive('get')->with(Hyde::path('_pages/foo.md'))->andReturn('foo');

        $object = MarkdownPage::parse('foo');
        $this->assertInstanceOf(MarkdownPage::class, $object);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostObject()
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->with(Hyde::path('_posts/foo.md'))->andReturn(false)
            ->shouldReceive('isFile')->with(Hyde::path('_posts/foo.md'))->andReturn(true)
            ->shouldReceive('get')->with(Hyde::path('_posts/foo.md'))->andReturn('foo');

        $object = MarkdownPost::parse('foo');
        $this->assertInstanceOf(MarkdownPost::class, $object);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageObject()
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->with(Hyde::path('_docs/foo.md'))->andReturn(false)
            ->shouldReceive('isFile')->with(Hyde::path('_docs/foo.md'))->andReturn(true)
            ->shouldReceive('get')->with(Hyde::path('_docs/foo.md'))->andReturn('foo');

        $object = DocumentationPage::parse('foo');
        $this->assertInstanceOf(DocumentationPage::class, $object);
    }
}
