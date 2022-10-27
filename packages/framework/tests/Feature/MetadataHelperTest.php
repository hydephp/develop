<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Meta
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

    public function test_get_method_returns_global_metadata_bag()
    {
        $this->assertEquals(Meta::get(), GlobalMetadataBag::make());
    }

    public function test_render_method_renders_global_metadata_bag()
    {
        $this->assertSame(Meta::render(), GlobalMetadataBag::make()->render());
    }
}
