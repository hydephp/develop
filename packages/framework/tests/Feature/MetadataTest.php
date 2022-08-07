<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Metadata\Metadata;
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
    }

    public function test_metadata_object_is_generated_automatically(): void
    {
        $page = new MarkdownPage();

        $this->assertNotNull($page->metadata);
        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertEquals([], $page->metadata->get());
    }

    public function test_link_item_can_be_added(): void
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'bar'),
        ], $page->metadata->links);
    }

    public function test_metadata_item_can_be_added(): void
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::name('foo', 'bar'),
        ], $page->metadata->metadata);
    }

    public function test_open_graph_item_can_be_added(): void
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::property('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::property('foo', 'bar'),
        ], $page->metadata->properties);
    }

    public function test_generic_item_can_be_added(): void
    {
        $page = new MarkdownPage();
        $page->metadata->add('foo');

        $this->assertEquals([
            'foo',
        ], $page->metadata->generics);
    }

    public function test_multiple_items_can_be_accessed_with_get_method(): void
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

    public function test_multiple_items_of_same_key_and_type_only_keeps_latest(): void
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::link('foo', 'baz'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'baz'),
        ], $page->metadata->links);
    }

    public function test_render_returns_html_string_of_imploded_metadata_arrays(): void
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
