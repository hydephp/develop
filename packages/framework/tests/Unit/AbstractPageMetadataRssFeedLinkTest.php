<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Contracts\AbstractPage::getDynamicMetadata
 */
class AbstractPageMetadataRssFeedLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'foo']);
    }

    protected function assertPageHasFeedLink($page)
    {
        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_markdown_posts()
    {
        $this->assertPageHasFeedLink(new MarkdownPost());
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_all_pages()
    {
        $this->assertPageHasFeedLink(new BladePage(''));
        $this->assertPageHasFeedLink(new MarkdownPage());
        $this->assertPageHasFeedLink(new MarkdownPost());
        $this->assertPageHasFeedLink(new DocumentationPage());
    }

    public function test_can_use_rss_feed_uses_configured_site_url()
    {
        config(['site.url' => 'foo']);
        $page = new MarkdownPost();

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_uses_configured_rss_file_name()
    {
        config(['hyde.rss_filename' => 'posts.rss']);
        $page = new MarkdownPost();

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/posts.rss" />',
            $page->renderPageMetadata()
        );
    }

    public function test_link_is_not_added_if_site_url_is_not_set()
    {
        config(['site.url' => '']);
        $page = new MarkdownPost();

        $this->assertStringNotContainsString(
            '<link rel="alternate" type="application/rss+xml"',
            $page->renderPageMetadata()
        );
    }
}
