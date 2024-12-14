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

class PageModelGetAllFilesHelperTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function tearDown(): void
    {
        $this->cleanupFilesystem();
    }

    public function testBladePageGetHelperReturnsBladePageArray()
    {
        $this->files(['_pages/test-page.blade.php']);

        HydeKernel::getInstance()->boot();

        $array = BladePage::files();
        $this->assertCount(3, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['404', 'index', 'test-page'], $array);
    }

    public function testMarkdownPageGetHelperReturnsMarkdownPageArray()
    {
        $this->files(['_pages/test-page.md']);

        HydeKernel::getInstance()->boot();

        $array = MarkdownPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }

    public function testMarkdownPostGetHelperReturnsMarkdownPostArray()
    {
        $this->files(['_posts/test-post.md']);

        HydeKernel::getInstance()->boot();

        $array = MarkdownPost::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-post'], $array);
    }

    public function testDocumentationPageGetHelperReturnsDocumentationPageArray()
    {
        $this->files(['_docs/test-page.md']);

        HydeKernel::getInstance()->boot();

        $array = DocumentationPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);
    }
}
