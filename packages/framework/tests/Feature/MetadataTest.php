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
}
