<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Concerns\AbstractPage::parse()
 */
class PageModelParseHelperTest extends TestCase
{
    public function test_blade_page_get_helper_returns_blade_page_object()
    {
        $object = BladePage::parse('foo');
        $this->assertInstanceOf(BladePage::class, $object);
    }

    public function test_markdown_page_get_helper_returns_markdown_page_object()
    {
        touch(Hyde::path('_pages/foo.md'));

        $object = MarkdownPage::parse('foo');
        $this->assertInstanceOf(MarkdownPage::class, $object);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_markdown_post_get_helper_returns_markdown_post_object()
    {
        touch(Hyde::path('_posts/foo.md'));

        $object = MarkdownPost::parse('foo');
        $this->assertInstanceOf(MarkdownPost::class, $object);

        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_documentation_page_get_helper_returns_documentation_page_object()
    {
        touch(Hyde::path('_docs/foo.md'));

        $object = DocumentationPage::parse('foo');
        $this->assertInstanceOf(DocumentationPage::class, $object);

        unlink(Hyde::path('_docs/foo.md'));
    }
}
