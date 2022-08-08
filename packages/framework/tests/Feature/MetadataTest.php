<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Metadata\LinkItem;
use Hyde\Framework\Models\Metadata\Metadata;
use Hyde\Framework\Models\Metadata\MetadataItem;
use Hyde\Framework\Models\Metadata\OpenGraphItem;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Metadata\Metadata
 * @covers \Hyde\Framework\Models\Metadata\LinkItem
 * @covers \Hyde\Framework\Models\Metadata\MetadataItem
 * @covers \Hyde\Framework\Models\Metadata\OpenGraphItem
 */
class MetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => null]);
        config(['hyde.meta' => []]);
        config(['hyde.generate_rss_feed' => false]);
        config(['site.generate_sitemap' => false]);
    }

    public function test_metadata_object_is_generated_automatically()
    {
        $page = new MarkdownPage();

        $this->assertNotNull($page->metadata);
        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertEquals([], $page->metadata->get());
    }

    public function test_link_item_model()
    {
        $item = new LinkItem('rel', 'href');
        $this->assertEquals('rel', $item->uniqueKey());
        $this->assertEquals('<link rel="rel" href="href">', (string) $item);

        $item = new LinkItem('rel', 'href', ['attr' => 'value']);
        $this->assertEquals('<link rel="rel" href="href" attr="value">', (string) $item);
    }

    public function test_metadata_item_model()
    {
        $item = new MetadataItem('name', 'content');
        $this->assertEquals('name', $item->uniqueKey());
        $this->assertEquals('<meta name="name" content="content">', (string) $item);
    }

    public function test_open_graph_item_model()
    {
        $item = new OpenGraphItem('property', 'content');
        $this->assertEquals('property', $item->uniqueKey());
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);

        $item = new OpenGraphItem('og:property', 'content');
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);
    }

    public function test_link_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'bar'),
        ], $page->metadata->links);
    }

    public function test_metadata_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::name('foo', 'bar'),
        ], $page->metadata->metadata);
    }

    public function test_open_graph_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::property('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::property('foo', 'bar'),
        ], $page->metadata->properties);
    }

    public function test_generic_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add('foo');

        $this->assertEquals([
            'foo',
        ], $page->metadata->generics);
    }

    public function test_multiple_items_can_be_accessed_with_get_method()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'foo',
        ], $page->metadata->get());
    }

    public function test_multiple_items_of_same_key_and_type_only_keeps_latest()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::link('foo', 'baz'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'baz'),
        ], $page->metadata->links);
    }

    public function test_render_returns_html_string_of_imploded_metadata_arrays()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals(implode("\n", [
            '<link rel="foo" href="bar">',
            '<meta name="foo" content="bar">',
            '<meta property="og:foo" content="bar">',
            'foo',
        ]),
        $page->metadata->render());
    }

    public function test_adds_config_defined_metadata()
    {
        config(['hyde.meta' => [
            Meta::link('foo', 'bar'),
            Meta::name('foo', 'bar'),
            Meta::property('foo', 'bar'),
            'foo' => 'bar',
            'baz',
        ]]);

        $page = new MarkdownPage();
        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'bar',
            'generics:1' => 'baz',
        ], $page->metadata->get());
    }

    public function test_automatically_adds_sitemap_when_enabled()
    {
        config(['site.url' => 'foo']);
        config(['site.generate_sitemap' => true]);

        $page = new MarkdownPage();

        $this->assertEquals('<link rel="sitemap" href="foo/sitemap.xml" type="application/xml" title="Sitemap">', $page->metadata->render());
    }

    public function test_sitemap_uses_configured_site_url()
    {
        config(['site.url' => 'bar']);
        config(['site.generate_sitemap' => true]);

        $page = new MarkdownPage();

        $this->assertEquals('<link rel="sitemap" href="bar/sitemap.xml" type="application/xml" title="Sitemap">', $page->metadata->render());
    }

    public function test_automatically_adds_rss_feed_when_enabled()
    {
        config(['site.url' => 'foo']);
        config(['hyde.generate_rss_feed' => true]);

        $page = new MarkdownPage();

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', $page->metadata->render());
    }

    public function test_rss_feed_uses_configured_site_url()
    {
        config(['site.url' => 'bar']);
        config(['hyde.generate_rss_feed' => true]);

        $page = new MarkdownPage();

        $this->assertEquals('<link rel="alternate" href="bar/feed.xml" type="application/rss+xml" title="HydePHP RSS Feed">', $page->metadata->render());
    }

    public function test_rss_feed_uses_configured_site_name()
    {
        config(['site.url' => 'foo']);
        config(['site.name' => 'Site']);
        config(['hyde.generate_rss_feed' => true]);

        $page = new MarkdownPage();

        $this->assertEquals('<link rel="alternate" href="foo/feed.xml" type="application/rss+xml" title="Site RSS Feed">', $page->metadata->render());
    }

    public function test_rss_feed_uses_configured_rss_file_name()
    {
        config(['site.url' => 'foo']);
        config(['hyde.rss_filename' => 'posts.rss']);
        config(['hyde.generate_rss_feed' => true]);
        $page = new MarkdownPage();

        $this->assertStringContainsString(
            '<link rel="alternate" href="foo/posts.rss" type="application/rss+xml" title="HydePHP RSS Feed">',
            $page->metadata->render()
        );
    }

    public function test_does_not_add_canonical_link_when_base_url_is_not_set()
    {
        config(['site.url' => null]);
        $page = MarkdownPage::make('bar');

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function test_does_not_add_canonical_link_when_identifier_is_not_set()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make();

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function test_adds_canonical_link_when_base_url_and_identifier_is_set()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar.html">', $page->metadata->render());
    }

    public function test_canonical_link_uses_clean_url_setting()
    {
        config(['site.url' => 'foo']);
        config(['site.pretty_urls' => true]);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar">', $page->metadata->render());
    }

    public function test_can_override_canonical_link_with_front_matter()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make('bar', [
            'canonicalUrl' => 'canonical',
        ]);
        $this->assertStringContainsString('<link rel="canonical" href="canonical">', $page->metadata->render());
    }

    public function test_adds_twitter_and_open_graph_title_when_title_is_set()
    {
        $page = MarkdownPage::make(matter: ['title' => 'Foo Bar']);

        $this->assertEquals(
            '<meta name="twitter:title" content="HydePHP - Foo Bar">'."\n".
            '<meta property="og:title" content="HydePHP - Foo Bar">',
            $page->metadata->render()
        );
    }

    public function test_does_not_add_twitter_and_open_graph_title_when_no_title_is_set()
    {
        $page = MarkdownPage::make(matter: ['title' => null]);

        $this->assertEquals('',
            $page->metadata->render()
        );
    }
}
