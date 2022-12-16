<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\Services\RebuildService;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * Test the Markdown page parser.
 */
class MarkdownPageTest extends TestCase
{
    public function test_can_get_collection_of_slugs()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $this->assertSame(['test-page'], DiscoveryService::getMarkdownPageFiles());
    }

    public function test_created_model_contains_expected_data()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $page = MarkdownPage::parse('test-page');

        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals("# Test Page \n Hello World!", $page->markdown);
        $this->assertEquals('test-page', $page->identifier);
    }

    public function test_can_render_markdown_page()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $page = MarkdownPage::parse('test-page');

        (new RebuildService($page->getSourcePath()))->execute();

        $this->assertFileExists(Hyde::path('_site/test-page.html'));
        $this->assertStringContainsString('<h1>Test Page</h1>',
            file_get_contents(Hyde::path('_site/test-page.html'))
        );

        unlink(Hyde::path('_site/test-page.html'));
    }
}
