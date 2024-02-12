<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Testing\TestCase;
use Illuminate\Support\HtmlString;

/**
 * @covers \Hyde\Markdown\Models\MarkdownDocument
 * @covers \Hyde\Markdown\Models\Markdown
 */
class MarkdownDocumentTest extends TestCase
{
    public function testConstructorCreatesNewMarkdownDocument()
    {
        $document = new MarkdownDocument([], '');
        $this->assertInstanceOf(MarkdownDocument::class, $document);
    }

    public function testConstructorArgumentsAreOptional()
    {
        $document = new MarkdownDocument();
        $this->assertInstanceOf(MarkdownDocument::class, $document);
    }

    public function testConstructorArgumentsAreAssigned()
    {
        $document = new MarkdownDocument(['foo' => 'bar'], 'Hello, world!');
        $this->assertEquals(FrontMatter::fromArray(['foo' => 'bar']), $document->matter);
    }

    public function testMagicToStringMethodReturnsBody()
    {
        $document = new MarkdownDocument(['foo' => 'bar'], 'Hello, world!');
        $this->assertEquals('Hello, world!', (string) $document);
    }

    public function testCompileMethodReturnsRenderedHtml()
    {
        $document = new MarkdownDocument([], 'Hello, world!');
        $this->assertEquals("<p>Hello, world!</p>\n", $document->markdown->compile());
    }

    public function testToHtmlMethodReturnsRenderedAsHtmlString()
    {
        $document = new MarkdownDocument([], 'Hello, world!');
        $this->assertInstanceOf(HtmlString::class, $document->markdown->toHtml());
        $this->assertEquals("<p>Hello, world!</p>\n", (string) $document->markdown->toHtml());
    }

    public function testParseMethodParsesAFileUsingTheMarkdownFileService()
    {
        file_put_contents('_pages/foo.md', "---\nfoo: bar\n---\nHello, world!");
        $document = MarkdownDocument::parse('_pages/foo.md');
        $this->assertInstanceOf(MarkdownDocument::class, $document);
        $this->assertEquals('Hello, world!', $document->markdown()->body());
        $this->assertEquals(FrontMatter::fromArray(['foo' => 'bar']), $document->matter());
        Filesystem::unlink('_pages/foo.md');
    }

    public function testToArrayMethodReturnsArrayMarkdownBodyLines()
    {
        $document = new MarkdownDocument(body: "foo\nbar\nbaz");
        $this->assertEquals(['foo', 'bar', 'baz'], $document->markdown->toArray());
    }

    public function testFromFileMethodReturnsNewMarkdownDocument()
    {
        file_put_contents('_pages/foo.md', "---\nfoo: bar\n---\nHello, world!");
        $markdown = Markdown::fromFile('_pages/foo.md');
        $this->assertInstanceOf(Markdown::class, $markdown);
        $this->assertEquals('Hello, world!', $markdown->body());
        Filesystem::unlink('_pages/foo.md');
    }

    public function end_of_markdown_body_is_trimmed()
    {
        $markdown = new Markdown("Hello, world!\n\r\t   ");
        $this->assertEquals('Hello, world!', $markdown->body());
    }

    public function testCarriageReturnsAreNormalized()
    {
        $markdown = new Markdown("foo\rbar");
        $this->assertEquals("foo\rbar", $markdown->body());

        $markdown = new Markdown("foo\r\nbar");
        $this->assertEquals("foo\nbar", $markdown->body());

        $markdown = new Markdown("foo\nbar");
        $this->assertEquals("foo\nbar", $markdown->body());
    }
}
