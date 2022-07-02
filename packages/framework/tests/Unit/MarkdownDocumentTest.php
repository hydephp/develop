<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\MarkdownDocument
 */
class MarkdownDocumentTest extends TestCase
{
    public function test_constructor_creates_new_markdown_document()
    {
        $document = new MarkdownDocument([], '');
        $this->assertInstanceOf(MarkdownDocument::class, $document);
    }

    public function test_constructor_arguments_are_optional()
    {
        $document = new MarkdownDocument();
        $this->assertInstanceOf(MarkdownDocument::class, $document);
    }

    public function test_constructor_arguments_are_assigned()
    {
        $document = new MarkdownDocument(['foo' => 'bar'], 'Hello, world!');
        $this->assertEquals(['foo' => 'bar'], $document->matter);
        $this->assertEquals('Hello, world!', $document->body);
    }

    public function test_magic_get_method_returns_front_matter_property()
    {
        $document = new MarkdownDocument(['foo' => 'bar']);
        $this->assertEquals('bar', $document->foo);
    }

    public function test_magic_get_method_returns_null_if_property_does_not_exist()
    {
        $document = new MarkdownDocument(['foo' => 'bar']);
        $this->assertNull($document->bar);
    }

    public function test_magic_to_string_method_returns_body()
    {
        $document = new MarkdownDocument(['foo' => 'bar'], 'Hello, world!');
        $this->assertEquals('Hello, world!', (string) $document);
    }

    public function test_matter_method_returns_empty_array_if_document_has_no_matter()
    {
        $document = new MarkdownDocument();
        $this->assertEquals([], $document->matter());
    }

    public function test_matter_method_returns_document_front_matter_array_if_no_arguments_are_specified()
    {
        $document = new MarkdownDocument(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $document->matter());
    }

    public function test_matter_method_returns_null_if_specified_front_matter_key_does_not_exist()
    {
        $document = new MarkdownDocument();
        $this->assertNull($document->matter('bar'));
    }

    public function test_matter_method_returns_specified_default_value_if_property_does_not_exist()
    {
        $document = new MarkdownDocument();
        $this->assertEquals('default', $document->matter('bar', 'default'));
    }

    public function test_matter_method_returns_specified_front_matter_value_if_key_is_specified()
    {
        $document = new MarkdownDocument(['foo' => 'bar']);
        $this->assertEquals('bar', $document->matter('foo'));
    }

    public function test_body_method_returns_empty_string_if_document_body_is_empty()
    {
        $document = new MarkdownDocument([], '');
        $this->assertEquals('', $document->body());
    }

    public function test_body_method_returns_document_body()
    {
        $document = new MarkdownDocument([], 'Hello, world!');
        $this->assertEquals('Hello, world!', $document->body());
    }

    public function test_render_method_returns_rendered_html()
    {
        $document = new MarkdownDocument([], 'Hello, world!');
        $this->assertEquals("<p>Hello, world!</p>\n", $document->render());
    }

    public function test_parse_file_method_parses_a_file_using_the_markdown_file_service()
    {
        file_put_contents('_pages/foo.md', 'Hello, world!');
        $document = MarkdownDocument::parseFile('_pages/foo.md');
        $this->assertInstanceOf(MarkdownDocument::class, $document);
        $this->assertEquals('Hello, world!', $document->body());
        $this->assertEquals([], $document->matter());
        unlink(Hyde::path('_pages/foo.md'));
    }
}
