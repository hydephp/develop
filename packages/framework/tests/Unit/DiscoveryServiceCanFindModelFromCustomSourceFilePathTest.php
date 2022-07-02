<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * Class DiscoveryServiceCanFindModelFromCustomSourceFilePathTest.
 *
 * @covers \Hyde\Framework\Services\DiscoveryService::findModelFromFilePath
 */
class DiscoveryServiceCanFindModelFromCustomSourceFilePathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        BladePage::$sourceDirectory = '.source/pages';
        MarkdownPage::$sourceDirectory = '.source/pages';
        MarkdownPost::$sourceDirectory = '.source/posts';
        DocumentationPage::$sourceDirectory = '.source/docs';
        Config::set('view.paths', ['.source/docs']);
    }

    public function test_method_can_find_blade_pages()
    {
        $this->assertEquals(
            BladePage::class,
            DiscoveryService::findModelFromFilePath('.source/pages/test.blade.php')
        );
    }

    public function test_method_can_find_markdown_pages()
    {
        $this->assertEquals(
            MarkdownPage::class,
            DiscoveryService::findModelFromFilePath('.source/pages/test.md')
        );
    }

    public function test_method_can_find_markdown_posts()
    {
        $this->assertEquals(
            MarkdownPost::class,
            DiscoveryService::findModelFromFilePath('.source/posts/test.md')
        );
    }

    public function test_method_can_find_documentation_pages()
    {
        $this->assertEquals(
            DocumentationPage::class,
            DiscoveryService::findModelFromFilePath('.source/docs/test.md')
        );
    }
}
