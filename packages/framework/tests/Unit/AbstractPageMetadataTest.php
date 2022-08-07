<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Contracts\AbstractPage
 */
class AbstractPageMetadataTest extends TestCase
{
    public function test_get_canonical_url_returns_url_for_top_level_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo');

        $this->assertEquals('https://example.com/foo.html', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_pretty_url_for_top_level_page()
    {
        config(['site.url' => 'https://example.com']);
        config(['site.pretty_urls' => true]);
        $page = new MarkdownPage('foo');

        $this->assertEquals('https://example.com/foo', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_url_for_nested_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo/bar');

        $this->assertEquals('https://example.com/foo/bar.html', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_url_for_deeply_nested_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo/bar/baz');

        $this->assertEquals('https://example.com/foo/bar/baz.html', $page->canonicalUrl);
    }

    public function test_canonical_url_is_not_set_when_identifier_is_null()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage();
        $this->assertNull($page->canonicalUrl);
        $this->assertStringNotContainsString(
            '<link rel="canonical"',
            $page->renderPageMetadata()
        );
    }

    public function test_canonical_url_is_not_set_when_site_url_is_null()
    {
        config(['site.url' => null]);
        $page = new MarkdownPage('foo');
        $this->assertNull($page->canonicalUrl);
        $this->assertStringNotContainsString(
            '<link rel="canonical"',
            $page->renderPageMetadata()
        );
    }

    public function test_custom_canonical_link_can_be_set_in_front_matter()
    {
        config(['site.url' => 'https://example.com']);
        $page = MarkdownPage::make(matter: ['canonicalUrl' => 'foo/bar']);
        $this->assertEquals('foo/bar', $page->canonicalUrl);
        $this->assertStringContainsString(
            '<link rel="canonical" href="foo/bar" />',
            $page->renderPageMetadata()
        );
    }

    public function test_render_page_metadata_returns_string()
    {
        $page = new MarkdownPage('foo');
        $this->assertIsString($page->renderPageMetadata());
    }

    public function test_render_page_metadata_returns_string_with_merged_metadata()
    {
        config(['site.url' => 'https://example.com']);
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);
        $page = new MarkdownPage('foo');

        $this->assertStringContainsString(
            '<meta name="foo" content="bar">'."\n".
            '<link rel="canonical" href="https://example.com/foo.html" />',
            $page->renderPageMetadata()
        );
    }

    public function test_render_page_metadata_only_adds_canonical_if_conditions_are_met()
    {
        config(['site.url' => null]);
        config(['hyde.meta' => []]);
        $page = new MarkdownPage('foo');

        $this->assertEquals(
            '',
            $page->renderPageMetadata()
        );
    }

    public function test_get_dynamic_metadata_only_adds_canonical_if_conditions_are_met()
    {
        config(['site.url' => null]);
        config(['hyde.meta' => []]);
        $page = new MarkdownPage('foo');

        $this->assertEquals(
            [],
            $page->getDynamicMetadata()
        );
    }

    public function test_get_dynamic_metadata_adds_canonical_url_when_conditions_are_met()
    {
        config(['site.url' => 'https://example.com']);
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);
        $page = new MarkdownPage('foo');

        $this->assertContains('<link rel="canonical" href="https://example.com/foo.html" />',
            $page->getDynamicMetadata()
        );
    }

    public function test_get_dynamic_metadata_adds_sitemap_link_when_conditions_are_met()
    {
        $page = new MarkdownPage('foo');

        config(['site.url' => 'https://example.com']);
        config(['site.generate_sitemap' => true]);

        $this->assertContains('<link rel="sitemap" type="application/xml" title="Sitemap" href="https://example.com/sitemap.xml" />',
            $page->getDynamicMetadata()
        );
    }

    public function test_get_dynamic_metadata_does_not_add_sitemap_link_when_conditions_are_not_met()
    {
        $page = new MarkdownPage('foo');

        config(['site.url' => 'https://example.com']);
        config(['site.generate_sitemap' => false]);

        $this->assertNotContains('<link rel="sitemap" type="application/xml" title="Sitemap" href="https://example.com/sitemap.xml" />',
            $page->getDynamicMetadata()
        );
    }

    public function test_has_twitter_title_in_config_returns_true_when_present_in_config()
    {
        config(['hyde.meta' => [
            Meta::name('twitter:title', 'foo'),
        ]]);

        $page = new MarkdownPage();

        $this->assertTrue($page->hasTwitterTitleInConfig());
    }

    public function test_has_twitter_title_in_config_returns_false_when_not_present_in_config()
    {
        config(['hyde.meta' => []]);

        $page = new MarkdownPage();

        $this->assertFalse($page->hasTwitterTitleInConfig());
    }

    public function test_has_open_graph_title_in_config_returns_true_when_present_in_config()
    {
        config(['hyde.meta' => [
            Meta::property('title', 'foo'),
        ]]);

        $page = new MarkdownPage();

        $this->assertTrue($page->hasOpenGraphTitleInConfig());
    }

    public function test_has_open_graph_title_in_config_returns_false_when_not_present_in_config()
    {
        config(['hyde.meta' => []]);

        $page = new MarkdownPage();

        $this->assertFalse($page->hasOpenGraphTitleInConfig());
    }

    public function test_get_dynamic_metadata_adds_twitter_and_open_graph_title_when_conditions_are_met()
    {
        config(['site.url' => null]);
        config(['hyde.meta' => [
            Meta::name('twitter:title', 'foo'),
            Meta::property('title', 'foo'),
        ]]);

        $page = MarkdownPage::make(matter: ['title' => 'Foo Bar']);

        $this->assertEquals([
            '<meta name="twitter:title" content="HydePHP - Foo Bar" />',
            '<meta property="og:title" content="HydePHP - Foo Bar" />',
        ],
            $page->getDynamicMetadata()
        );
    }
}
