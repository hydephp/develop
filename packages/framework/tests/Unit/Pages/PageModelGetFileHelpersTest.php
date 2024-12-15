<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Foundation\HydeKernel;
use Hyde\Pages\BladePage;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Testing\UnitTestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Collection;

class PageModelGetFileHelpersTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testBladePageFilesHelperReturnsBladePageArray()
    {
        $this->withFile('_pages/test-page.blade.php');

        $array = BladePage::files();
        $this->assertCount(3, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['404', 'index', 'test-page'], $array);
    }

    public function testMarkdownPageFilesHelperReturnsMarkdownPageArray()
    {
        $this->withFile('_pages/test-page.md');

        $array = MarkdownPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    public function testMarkdownPostFilesHelperReturnsMarkdownPostArray()
    {
        $this->withFile('_posts/test-post.md');

        $array = MarkdownPost::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-post'], $array);
    }

    public function testDocumentationPageFilesHelperReturnsDocumentationPageArray()
    {
        $this->withFile('_docs/test-page.md');

        $array = DocumentationPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    public function testBladePageAllHelperReturnsBladePageCollection()
    {
        $this->withFile('_pages/test-page.blade.php');

        $collection = BladePage::all();

        $this->assertCount(3, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(BladePage::class, $collection);
    }

    public function testMarkdownPageAllHelperReturnsMarkdownPageCollection()
    {
        $this->withFile('_pages/test-page.md');

        $collection = MarkdownPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPage::class, $collection);
    }

    public function testMarkdownPostAllHelperReturnsMarkdownPostCollection()
    {
        $this->withFile('_posts/test-post.md');

        $collection = MarkdownPost::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(MarkdownPost::class, $collection);
    }

    public function testDocumentationPageAllHelperReturnsDocumentationPageCollection()
    {
        $this->withFile('_docs/test-page.md');

        $collection = DocumentationPage::all();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertContainsOnlyInstancesOf(DocumentationPage::class, $collection);
    }

    protected function withFile(string $path): void
    {
        $this->file($path);

        HydeKernel::getInstance()->boot();
    }

    protected function tearDown(): void
    {
        $this->cleanupFilesystem();
    }
}
