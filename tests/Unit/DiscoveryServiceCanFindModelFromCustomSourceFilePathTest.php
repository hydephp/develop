<?php

namespace Tests\Unit;

use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Class DiscoveryServiceCanFindModelFromCustomSourceFilePathTest.
 *
 * @covers \Hyde\Framework\DiscoveryService::findModelFromFilePath()
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
