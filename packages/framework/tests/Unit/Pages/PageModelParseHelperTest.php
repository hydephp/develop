<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\Concerns\HydePage::class)]
class PageModelParseHelperTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();
    }

    public function testBladePageGetHelperReturnsBladePageObject()
    {
        $this->mockFilesystemCalls('_pages/foo.blade.php');

        $this->assertInstanceOf(BladePage::class, BladePage::parse('foo'));
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageObject()
    {
        $this->mockFilesystemCalls('_pages/foo.md');

        $this->assertInstanceOf(MarkdownPage::class, MarkdownPage::parse('foo'));
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostObject()
    {
        $this->mockFilesystemCalls('_posts/foo.md');

        $this->assertInstanceOf(MarkdownPost::class, MarkdownPost::parse('foo'));
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageObject()
    {
        $this->mockFilesystemCalls('_docs/foo.md');

        $this->assertInstanceOf(DocumentationPage::class, DocumentationPage::parse('foo'));
    }

    protected function mockFilesystemCalls(string $path): void
    {
        $this->mockFilesystemStrict()
            ->shouldReceive('missing')->once()->with(Hyde::path($path))->andReturn(false)
            ->shouldReceive('get')->once()->with(Hyde::path($path))->andReturn('foo');
    }
}
