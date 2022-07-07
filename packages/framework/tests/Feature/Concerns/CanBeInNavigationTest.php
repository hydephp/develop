<?php

namespace Hyde\Framework\Testing\Feature\Concerns;

use Hyde\Framework\Concerns\CanBeInNavigation;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Pages\DocumentationPage;

/**
 * @covers \Hyde\Framework\Concerns\CanBeInNavigation
 */
class CanBeInNavigationTest extends TestCase
{
    // test showInNavigation returns false for MarkdownPost
    public function test_show_in_navigation_returns_false_for_markdown_post()
    {
        $page = $this->mock(MarkdownPost::class)->makePartial();

        $this->assertFalse($page->showInNavigation());
    }

    // test showInNavigation returns true for DocumentationPage if slug is index
    public function test_show_in_navigation_returns_true_for_documentation_page_if_slug_is_index()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->slug = 'index';

        $this->assertTrue($page->showInNavigation());
    }

    // test showInNavigation returns false for DocumentationPage if slug is not index
    public function test_show_in_navigation_returns_false_for_documentation_page_if_slug_is_not_index()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->slug = 'not-index';

        $this->assertFalse($page->showInNavigation());
    }

    // test showInNavigation returns false for AbstractMarkdownPage if matter('navigation.hidden', false) is true
    public function test_show_in_navigation_returns_false_for_abstract_markdown_page_if_matter_navigation_hidden_is_true()
    {
        $page = $this->mock(AbstractMarkdownPage::class)->makePartial();
        $page->markdown = $this->mock(MarkdownDocument::class)->makePartial();
        $page->markdown->shouldReceive('matter')->with('navigation.hidden', false)->andReturn(true);

        $this->assertFalse($page->showInNavigation());
    }

    // test showInNavigation returns true for AbstractMarkdownPage if matter('navigation.hidden', false) is false
    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_false()
    {
        $page = $this->mock(AbstractMarkdownPage::class)->makePartial();
        $page->slug = 'foo';
        $page->markdown = $this->mock(MarkdownDocument::class)->makePartial();
        $page->markdown->shouldReceive('matter')->with('navigation.hidden', false)->andReturn(false);

        $this->assertTrue($page->showInNavigation());
    }

    // test showInNavigation returns true for AbstractMarkdownPage if matter('navigation.hidden', false) is not set
    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_not_set()
    {
        $page = $this->mock(AbstractMarkdownPage::class)->makePartial();
        $page->slug = 'foo';
        $page->markdown = $this->mock(MarkdownDocument::class)->makePartial();
        $page->markdown->shouldReceive('matter')->with('navigation.hidden', false)->andReturn(null);

        $this->assertTrue($page->showInNavigation());
    }

    // test showInNavigation returns false if slug is present in config('hyde.navigation.exclude', ['404'])
    public function test_show_in_navigation_returns_false_if_slug_is_present_in_config_hyde_navigation_exclude()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'foo';

        $this->assertTrue($page->showInNavigation());

        config(['hyde.navigation.exclude' => ['foo']]);
        $this->assertFalse($page->showInNavigation());
    }

    // test showInNavigation returns false if slug is 404
    public function test_show_in_navigation_returns_true_if_slug_is_404()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = '404';

        $this->assertFalse($page->showInNavigation());
    }

    // test showInNavigation defaults to true if all checks pass
    public function test_show_in_navigation_defaults_to_true_if_all_checks_pass()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'foo';

        $this->assertTrue($page->showInNavigation());
    }

    // test navigationMenuPriority returns front matter value of navigation.priority if AbstractMarkdownPage and not null
    public function test_navigation_menu_priority_returns_front_matter_value_of_navigation_priority_if_abstract_markdown_page_and_not_null()
    {
        $page = $this->mock(AbstractMarkdownPage::class)->makePartial();
        $page->markdown = $this->mock(MarkdownDocument::class)->makePartial();
        $page->markdown->shouldReceive('matter')->with('navigation.priority', null)->andReturn(1);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority returns specified config value if slug exists in config('hyde.navigation.order', [])
    public function test_navigation_menu_priority_returns_specified_config_value_if_slug_exists_in_config_hyde_navigation_order()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'foo';

        $this->assertEquals(1000, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 1]]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority gives precedence to front matter over config('hyde.navigation.order', [])
    public function test_navigation_menu_priority_gives_precedence_to_front_matter_over_config_hyde_navigation_order()
    {
        $page = $this->mock(AbstractMarkdownPage::class)->makePartial();
        $page->markdown = $this->mock(MarkdownDocument::class)->makePartial();
        $page->markdown->shouldReceive('matter')->with('navigation.priority', null)->andReturn(1);
        $page->slug = 'foo';

        $this->assertEquals(1, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 2]]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority returns 100 for DocumentationPage
    public function test_navigation_menu_priority_returns_100_for_documentation_page()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'foo';

        $this->assertEquals(100, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority returns 0 if slug is index
    public function test_navigation_menu_priority_returns_0_if_slug_is_index()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'index';

        $this->assertEquals(0, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority does not return 0 if slug is index but model is documentation page
    public function test_navigation_menu_priority_does_not_return_0_if_slug_is_index_but_model_is_documentation_page()
    {
        $page = $this->mock(DocumentationPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'index';

        $this->assertEquals(100, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority returns 10 if slug is posts
    public function test_navigation_menu_priority_returns_10_if_slug_is_posts()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'posts';

        $this->assertEquals(10, $page->navigationMenuPriority());
    }

    // test navigationMenuPriority defaults to 1000 if no other conditions are met
    public function test_navigation_menu_priority_defaults_to_1000_if_no_other_conditions_are_met()
    {
        $page = $this->mock(MarkdownPage::class)->makePartial();
        $page->markdown = new MarkdownDocument();
        $page->slug = 'foo';

        $this->assertEquals(1000, $page->navigationMenuPriority());
    }

    // test navigationMenuTitle
    public function test_navigation_menu_title()
    {

    }
}
