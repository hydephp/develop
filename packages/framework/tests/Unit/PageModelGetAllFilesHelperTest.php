<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Concerns\HydePage::files()
 */
class PageModelGetAllFilesHelperTest extends TestCase
{
    public function test_blade_page_get_helper_returns_blade_page_array()
    {
        $array = BladePage::files();
        $this->assertCount(2, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['404', 'index'], $array);
    }

    public function test_markdown_page_get_helper_returns_markdown_page_array()
    {
        Hyde::touch(('_pages/test-page.md'));

        $array = MarkdownPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);

        unlink(Hyde::path('_pages/test-page.md'));
    }

    public function test_markdown_post_get_helper_returns_markdown_post_array()
    {
        Hyde::touch(('_posts/test-post.md'));

        $array = MarkdownPost::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-post'], $array);

        unlink(Hyde::path('_posts/test-post.md'));
    }

    public function test_documentation_page_get_helper_returns_documentation_page_array()
    {
        Hyde::touch(('_docs/test-page.md'));

        $array = DocumentationPage::files();
        $this->assertCount(1, $array);
        $this->assertIsArray($array);
        $this->assertEquals(['test-page'], $array);

        unlink(Hyde::path('_docs/test-page.md'));
    }
}
