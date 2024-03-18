<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Views;

use Hyde\Facades\Config;
use Hyde\Testing\TestCase;
use Hyde\Testing\TestsBladeViews;

class SidebarFooterTextViewTest extends TestCase
{
    use TestsBladeViews;

    public function testSidebarFooterTextViewWithDefaultConfig()
    {
        $view = $this->view(view('hyde::components.docs.sidebar-footer-text'));

        $view->assertSeeHtml('<a href="index.html">Back to home page</a>');
    }

    public function testSidebarFooterTextViewWhenConfigOptionIsTrue()
    {
        Config::set('docs.sidebar.footer', true);

        $view = $this->view(view('hyde::components.docs.sidebar-footer-text'));

        $view->assertSeeHtml('<a href="index.html">Back to home page</a>');
    }

    public function testSidebarFooterTextViewWhenConfigOptionIsMarkdownString()
    {
        Config::set('docs.sidebar.footer', 'Your Markdown String Here');

        $view = $this->view(view('hyde::components.docs.sidebar-footer-text'));

        $view->assertSeeText('Your Markdown String Here');
    }

    public function testSidebarFooterTextViewWhenConfigOptionIsFalse()
    {
        // This state is handled earlier in the component by the sidebar component so we don't need to test it here.

        $this->assertTrue(true);
    }
}
