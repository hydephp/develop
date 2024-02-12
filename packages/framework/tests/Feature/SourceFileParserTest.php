<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\SourceFileParser
 */
class SourceFileParserTest extends TestCase
{
    public function testBladePageParser()
    {
        $this->file('_pages/foo.blade.php');

        $parser = new SourceFileParser(BladePage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(BladePage::class, $page);
        $this->assertEquals('foo', $page->identifier);
    }

    public function testMarkdownPageParser()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function testMarkdownPostParser()
    {
        $this->markdown('_posts/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPost::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPost::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function testDocumentationPageParser()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(DocumentationPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(DocumentationPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function testHtmlPageParser()
    {
        $this->file('_pages/foo.html', '<h1>Foo Bar</h1>');

        $parser = new SourceFileParser(HtmlPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(HtmlPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('<h1>Foo Bar</h1>', $page->contents());
    }

    public function testParsedPageIsRunThroughDynamicConstructor()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);
        $page = MarkdownPage::parse('foo');
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function testBladePageDataIsParsedToFrontMatter()
    {
        $this->file('_pages/foo.blade.php', "@php(\$foo = 'bar')\n");
        $page = BladePage::parse('foo');
        $this->assertEquals('bar', $page->data('foo'));
    }

    public function testBladePageMatterIsUsedForThePageTitle()
    {
        $this->file('_pages/foo.blade.php', "@php(\$title = 'Foo Bar')\n");
        $page = BladePage::parse('foo');
        $this->assertEquals('Foo Bar', $page->data('title'));
    }
}
