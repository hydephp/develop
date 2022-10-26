<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Markdown\FrontMatter;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Markdown\FrontMatter
 */
class FrontMatterModelTest extends TestCase
{
    public function test_constructor_creates_new_front_matter_model()
    {
        $matter = new FrontMatter([]);
        $this->assertInstanceOf(FrontMatter::class, $matter);
    }

    public function test_constructor_arguments_are_optional()
    {
        $matter = new FrontMatter();
        $this->assertInstanceOf(FrontMatter::class, $matter);
    }

    public function test_constructor_arguments_are_assigned()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $matter->data);
    }

    public function test_static_from_array_method_creates_new_front_matter_model()
    {
        $matter = FrontMatter::fromArray(['foo' => 'bar']);
        $this->assertInstanceOf(FrontMatter::class, $matter);
        $this->assertEquals(['foo' => 'bar'], $matter->data);
    }

    public function test_to_string_magic_method_converts_model_array_into_yaml_front_matter()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals("---\nfoo: bar\n---\n", (string) $matter);
    }

    public function test_magic_get_method_returns_front_matter_property()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals('bar', $matter->foo);
    }

    public function test_magic_get_method_returns_null_if_property_does_not_exist()
    {
        $matter = new FrontMatter();
        $this->assertNull($matter->foo);
    }

    public function test_get_method_returns_empty_array_if_document_has_no_matter()
    {
        $matter = new FrontMatter();
        $this->assertEquals([], $matter->get());
    }

    public function test_get_method_returns_document_front_matter_array_if_no_arguments_are_specified()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $matter->get());
    }

    public function test_get_method_returns_null_if_specified_front_matter_key_does_not_exist()
    {
        $matter = new FrontMatter();
        $this->assertNull($matter->get('bar'));
    }

    public function test_get_method_returns_specified_default_value_if_property_does_not_exist()
    {
        $matter = new FrontMatter();
        $this->assertEquals('default', $matter->get('bar', 'default'));
    }

    public function test_get_method_returns_specified_front_matter_value_if_key_is_specified()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals('bar', $matter->get('foo'));
    }

    public function test_set_method_sets_front_matter_property()
    {
        $matter = new FrontMatter();
        $matter->set('foo', 'bar');
        $this->assertEquals('bar', $matter->get('foo'));
    }

    public function test_set_method_returns_self()
    {
        $matter = new FrontMatter();
        $this->assertSame($matter, $matter->set('foo', 'bar'));
    }

    public function test_has_method_returns_true_if_property_exists()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertTrue($matter->has('foo'));
    }

    public function test_has_method_returns_false_if_property_does_not_exist()
    {
        $matter = new FrontMatter();
        $this->assertFalse($matter->has('foo'));
    }

    public function test_to_array_returns_front_matter_array()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $matter->toArray());
    }
}
