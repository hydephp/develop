<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * Test the constructor actions and schema constructors for page models.
 *
 * @covers \Hyde\Framework\Actions\Constructors\FindsTitleForPage
 * @covers \Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage
 * @covers \Hyde\Framework\Concerns\Internal\ConstructsPageSchemas
 */
class PageModelConstructorsTest extends TestCase
{
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

    public function test_documentation_page_parser_can_get_group_from_front_matter()
    {
        $this->markdown('_docs/foo.md', '# Foo Bar', ['navigation.group' => 'foo']);

        $page = DocumentationPage::parse('foo');
        $this->assertEquals('foo', $page->navigationMenuGroup());
    }

    public function test_documentation_page_parser_can_get_group_automatically_from_nested_page()
    {
        mkdir(Hyde::path('_docs/foo'));
        touch(Hyde::path('_docs/foo/bar.md'));

        /** @var DocumentationPage $page */
        $page = DocumentationPage::parse('foo/bar');
        $this->assertEquals('foo', $page->navigationMenuGroup());

        unlink(Hyde::path('_docs/foo/bar.md'));
        rmdir(Hyde::path('_docs/foo'));
    }
}
