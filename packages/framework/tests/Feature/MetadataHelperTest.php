<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Helpers\Meta
 */
class MetadataHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.meta' => []]);
        config(['site.url' => null]);
    }

    public function test_name_method_returns_a_valid_html_meta_string()
    {
        $this->assertEquals(
            '<meta name="foo" content="bar">',
            Meta::name('foo', 'bar')
        );
    }

    public function test_property_method_returns_a_valid_html_meta_string()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('foo', 'bar')
        );
    }

    public function test_property_method_accepts_property_with_og_prefix()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('og:foo', 'bar')
        );
    }

    public function test_property_method_accepts_property_without_og_prefix()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('foo', 'bar')
        );
    }

    public function test_link_method_returns_a_valid_html_link_string()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar">',
            Meta::link('foo', 'bar')
        );
    }

    public function test_link_method_returns_a_valid_html_link_string_with_attributes()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar" title="baz">',
            Meta::link('foo', 'bar', ['title' => 'baz'])
        );
    }

    public function test_link_method_returns_a_valid_html_link_string_with_multiple_attributes()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar" title="baz" type="text/css">',
            Meta::link('foo', 'bar', ['title' => 'baz', 'type' => 'text/css'])
        );
    }

    public function test_get_method_returns_global_metadata_merged_with_argument()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(
            [
                '<meta name="foo" content="bar">',
                '<meta name="bar" content="baz">',
            ],
            Meta::get([Meta::name('bar', 'baz')])
        );
    }

    public function test_render_method_implodes_an_array_of_meta_tags_into_a_formatted_string()
    {
        $this->assertEquals(
            '<meta name="foo" content="bar">'
            ."\n".'<meta property="og:foo" content="bar">',

            Meta::render([
                Meta::name('foo', 'bar'),
                Meta::property('og:foo', 'bar'),
            ])
        );
    }

    public function test_render_method_returns_an_empty_string_if_no_meta_tags_are_supplied()
    {
        $this->assertEquals(
            '',
            Meta::render([])
        );
    }

    public function test_render_method_returns_config_defined_tags_if_no_meta_tags_are_supplied()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
            Meta::property('og:foo', 'bar'),
        ]]);

        $this->assertEquals(
            '<meta name="foo" content="bar">'
            ."\n".'<meta property="og:foo" content="bar">',

            Meta::render([])
        );
    }

    public function test_render_method_merges_config_defined_tags_with_supplied_meta_tags()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(
            '<meta name="foo" content="bar">'
            ."\n".'<meta property="og:foo" content="bar">',

            Meta::render([
                Meta::property('foo', 'bar'),
            ])
        );
    }

    public function test_render_method_returns_unique_meta_tags()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(
            '<meta name="foo" content="bar">',
            Meta::render([
                Meta::name('foo', 'bar'),
            ])
        );
    }

    public function test_render_method_gives_precedence_to_supplied_meta_tags()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);

        $this->assertEquals(
            '<meta name="foo" content="baz">',

            Meta::render([
                Meta::name('foo', 'baz'),
            ])
        );
    }

    public function test_get_dynamic_metadata_adds_sitemap_link_when_conditions_are_met()
    {
        config(['site.url' => 'https://example.com']);
        config(['site.generate_sitemap' => true]);
        $page = new MarkdownPage('foo');

        $this->assertStringContainsString('<link rel="sitemap" href="https://example.com/sitemap.xml" type="application/xml" title="Sitemap">',
            $page->renderPageMetadata()
        );
    }

    public function test_get_dynamic_metadata_does_not_add_sitemap_link_when_conditions_are_not_met()
    {
        $page = new MarkdownPage('foo');

        config(['site.url' => 'https://example.com']);
        config(['site.generate_sitemap' => false]);

        $this->assertStringNotContainsString('<link rel="sitemap" type="application/xml" title="Sitemap" href="https://example.com/sitemap.xml">',
            $page->renderPageMetadata()
        );
    }

    protected function assertPageHasFeedLink($page)
    {
        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_markdown_posts()
    {
        config(['site.url' => 'foo']);
        $this->assertPageHasFeedLink(new MarkdownPost());
    }

    public function test_can_use_rss_feed_link_adds_meta_link_for_all_pages()
    {
        config(['site.url' => 'foo']);
        $this->assertPageHasFeedLink(new BladePage(''));
        $this->assertPageHasFeedLink(new MarkdownPage());
        $this->assertPageHasFeedLink(new MarkdownPost());
        $this->assertPageHasFeedLink(new DocumentationPage());
    }

    public function test_can_use_rss_feed_uses_configured_site_url()
    {
        config(['site.url' => 'foo']);
        config(['site.url' => 'foo']);
        $page = new MarkdownPage();

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">',
            $page->renderPageMetadata()
        );
    }

    public function test_can_use_rss_feed_uses_configured_rss_file_name()
    {
        config(['site.url' => 'foo']);
        config(['hyde.rss_filename' => 'posts.rss']);
        $page = new MarkdownPage();

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/posts.rss" type="application/rss+xml" title="HydePHP RSS Feed">',
            $page->renderPageMetadata()
        );
    }

    public function test_link_is_not_added_if_site_url_is_not_set()
    {
        config(['site.url' => 'foo']);
        config(['site.url' => '']);
        $page = new MarkdownPage();

        $this->assertStringNotContainsString(
            '<link rel="alternate" type="application/rss+xml"',
            $page->renderPageMetadata()
        );
    }
}
