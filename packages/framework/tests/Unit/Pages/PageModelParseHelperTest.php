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
        $this->mockFilesystemCalls('_pages/foo.blade.php');

        $object = BladePage::parse('foo');
        $this->assertInstanceOf(BladePage::class, $object);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageObject()
    {
        $this->mockFilesystemCalls('_pages/foo.md');

        $object = MarkdownPage::parse('foo');
        $this->assertInstanceOf(MarkdownPage::class, $object);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostObject()
    {
        $this->mockFilesystemCalls('_posts/foo.md');

        $object = MarkdownPost::parse('foo');
        $this->assertInstanceOf(MarkdownPost::class, $object);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageObject()
    {
        $this->mockFilesystemCalls('_docs/foo.md');

        $object = DocumentationPage::parse('foo');
        $this->assertInstanceOf(DocumentationPage::class, $object);
    }

    protected function mockFilesystemCalls(string $path): void
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->with(Hyde::path($path))->andReturn(false)
            ->shouldReceive('isFile')->with(Hyde::path($path))->andReturn(true)
            ->shouldReceive('get')->with(Hyde::path($path))->andReturn('foo');
    }
}
