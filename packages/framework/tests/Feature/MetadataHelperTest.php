<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Meta;
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
}
