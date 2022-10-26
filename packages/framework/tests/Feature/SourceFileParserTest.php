<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\HtmlPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\SourceFileParser
 */
class SourceFileParserTest extends TestCase
{
    public function test_blade_page_parser()
    {
        $this->file('_pages/foo.blade.php');

        $parser = new SourceFileParser(BladePage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(BladePage::class, $page);
        $this->assertEquals('foo', $page->identifier);
    }

    public function test_markdown_page_parser()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_markdown_post_parser()
    {
        $this->markdown('_posts/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPost::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPost::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_documentation_page_parser()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(DocumentationPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(DocumentationPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('# Foo Bar', $page->markdown);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_html_page_parser()
    {
        $this->file('_pages/foo.html', '<h1>Foo Bar</h1>');

        $parser = new SourceFileParser(HtmlPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(HtmlPage::class, $page);
        $this->assertEquals('foo', $page->identifier);
        $this->assertEquals('<h1>Foo Bar</h1>', $page->contents());
    }

    public function test_parsed_page_is_run_through_dynamic_constructor()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);
        $page = MarkdownPage::parse('foo');
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_blade_page_data_is_parsed_to_front_matter()
    {
        $this->file('_pages/foo.blade.php', "@php(\$foo = 'bar')\n");
        $page = BladePage::parse('foo');
        $this->assertEquals('bar', $page->get('foo'));
    }

    public function test_blade_page_matter_is_used_for_the_page_title()
    {
        $this->file('_pages/foo.blade.php', "@php(\$title = 'Foo Bar')\n");
        $page = BladePage::parse('foo');
        $this->assertEquals('Foo Bar', $page->get('title'));
    }
}
