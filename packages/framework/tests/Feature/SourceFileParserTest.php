<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
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
        $this->assertEquals('foo', $page->slug);
    }

    public function test_markdown_page_parser()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPage::class, $page);
        $this->assertEquals('foo', $page->slug);
        $this->assertEquals('# Foo Bar', $page->body);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_markdown_post_parser()
    {
        $this->markdown('_posts/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(MarkdownPost::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(MarkdownPost::class, $page);
        $this->assertEquals('foo', $page->slug);
        $this->assertEquals('# Foo Bar', $page->body);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_documentation_page_parser()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['title' => 'Foo Bar Baz']);

        $parser = new SourceFileParser(DocumentationPage::class, 'foo');
        $page = $parser->get();
        $this->assertInstanceOf(DocumentationPage::class, $page);
        $this->assertEquals('foo', $page->slug);
        $this->assertEquals('# Foo Bar', $page->body);
        $this->assertEquals('Foo Bar Baz', $page->title);
    }

    public function test_dynamic_data_constructor_can_find_title_from_front_matter()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar', ['title' => 'My Title']);
        $page = MarkdownPage::parse('foo');
        $this->assertEquals('My Title', $page->title);
    }

    public function test_dynamic_data_constructor_can_find_title_from_h1_tag()
    {
        $this->markdown('_pages/foo.md', '# Foo Bar');
        $page = MarkdownPage::parse('foo');

        $this->assertEquals('Foo Bar', $page->title);
    }

    public function test_dynamic_data_constructor_can_find_title_from_slug()
    {
        $this->markdown('_pages/foo-bar.md');
        $page = MarkdownPage::parse('foo-bar');

        $this->assertEquals('Foo Bar', $page->title);
    }
}
