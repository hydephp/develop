<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\HasPageMetadata::getDynamicMetadata
 */
class HasPageMetadataRssFeedLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'foo']);
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_markdown_posts()
    {
        $page = new MarkdownPost([], '');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_post_related_pages()
    {
        $page = new MarkdownPage([], '', identifier: 'posts');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_markdown_index_page()
    {
        $page = new MarkdownPage([], '', identifier: 'index');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_blade_index_page()
    {
        $page = new BladePage('index');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_uses_configured_site_url()
    {
        config(['site.url' => 'https://example.org']);
        $page = new MarkdownPost([], '');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="https://example.org/feed.xml" />',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_uses_configured_rss_file_name()
    {
        config(['hyde.rss_filename' => 'posts.rss']);
        $page = new MarkdownPost([], '');

        $this->assertStringContainsString(
            '<link rel="alternate" type="application/rss+xml" title="HydePHP RSS Feed" href="foo/posts.rss" />',
            $page->renderPageMetadata()
        );
    }

    public function test_link_is_not_added_if_site_url_is_not_set()
    {
        config(['site.url' => '']);
        $page = new MarkdownPost([], '');

        $this->assertStringNotContainsString(
            '<link rel="alternate" type="application/rss+xml"',
            $page->renderPageMetadata()
        );
    }
}
