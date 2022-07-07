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

    // test navigationMenuPriority
    public function test_navigation_menu_priority()
    {

    }


    // test navigationMenuTitle
    public function test_navigation_menu_title()
    {

    }
}
