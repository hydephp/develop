<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * Test the Markdown page parser.
 */
class MarkdownPageTest extends TestCase
{
    public function testCanGetCollectionOfSlugs()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $this->assertSame(['test-page'], MarkdownPage::files());
    }

    public function testCreatedModelContainsExpectedData()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $page = MarkdownPage::parse('test-page');

        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals("# Test Page \n Hello World!", $page->markdown);
        $this->assertEquals('test-page', $page->identifier);
    }

    public function testCanRenderMarkdownPage()
    {
        $this->file('_pages/test-page.md', "# Test Page \n Hello World!");
        $page = MarkdownPage::parse('test-page');

        StaticPageBuilder::handle(Pages::getPage($page->getSourcePath()));

        $this->assertFileExists(Hyde::path('_site/test-page.html'));
        $this->assertStringContainsString('<h1>Test Page</h1>',
            file_get_contents(Hyde::path('_site/test-page.html'))
        );

        Filesystem::unlink('_site/test-page.html');
    }
}
