<?php

namespace Hyde\Framework\Testing\Feature\Concerns;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\CanBeInNavigation
 */
class CanBeInNavigationTest extends TestCase
{
    public function test_show_in_navigation_returns_false_for_markdown_post()
    {
        $page = $this->mock(MarkdownPost::class)->makePartial();

        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_documentation_page_if_slug_is_index()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->identifier = 'index';

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_for_documentation_page_if_slug_is_not_index()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->identifier = 'not-index';

        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_for_abstract_markdown_page_if_matter_navigation_hidden_is_true()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => true]);

        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_false()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => false]);

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_not_set()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => null]);

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_if_slug_is_present_in_config_hyde_navigation_exclude()
    {
        $page = MarkdownPage::make('foo');

        $this->assertTrue($page->showInNavigation());

        config(['hyde.navigation.exclude' => ['foo']]);
        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_if_slug_is_404()
    {
        $page = MarkdownPage::make('404');
        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_defaults_to_true_if_all_checks_pass()
    {
        $page = MarkdownPage::make('foo');
        $this->assertTrue($page->showInNavigation());
    }

    public function test_navigation_menu_priority_returns_front_matter_value_of_navigation_priority_if_abstract_markdown_page_and_not_null()
    {
        $page = MarkdownPage::make('foo', ['navigation.priority' => 1]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_specified_config_value_if_slug_exists_in_config_hyde_navigation_order()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals(999, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 1]]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_gives_precedence_to_front_matter_over_config_hyde_navigation_order()
    {
        $page = MarkdownPage::make('foo', ['navigation.priority' => 1]);

        $this->assertEquals(1, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 2]]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_100_for_documentation_page()
    {
        $page = DocumentationPage::make('foo');
        $this->assertEquals(100, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_0_if_slug_is_index()
    {
        $page = MarkdownPage::make('index');
        $this->assertEquals(0, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_does_not_return_0_if_slug_is_index_but_model_is_documentation_page()
    {
        $page = DocumentationPage::make('index');
        $this->assertEquals(100, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_10_if_slug_is_posts()
    {
        $page = MarkdownPage::make('posts');
        $this->assertEquals(10, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_defaults_to_999_if_no_other_conditions_are_met()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals(999, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_title_returns_navigation_title_matter_if_set()
    {
        $page = MarkdownPage::make('foo', ['navigation.title' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_returns_title_matter_if_set()
    {
        $page = MarkdownPage::make('foo', ['title' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_navigation_title_has_precedence_over_title()
    {
        $page = MarkdownPage::make('foo', ['title' => 'foo', 'navigation.title' => 'bar']);
        $this->assertEquals('bar', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_returns_docs_if_slug_is_index_and_model_is_documentation_page()
    {
        $page = DocumentationPage::make('index');
        $this->assertEquals('Docs', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_returns_home_if_slug_is_index_and_model_is_not_documentation_page()
    {
        $page = MarkdownPage::make('index');
        $this->assertEquals('Home', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_returns_title_if_title_is_set_and_not_empty()
    {
        $page = MarkdownPage::make('bar', ['title' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuTitle());
    }

    public function test_navigation_menu_title_falls_back_to_hyde_make_title_from_slug()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals('Foo', $page->navigationMenuTitle());
    }
}
