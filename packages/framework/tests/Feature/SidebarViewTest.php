<?php

/** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\View\View;
use Throwable;
use function view;

/**
 * Very high level test of the sidebar views and their combinations of layouts.
 */
class SidebarViewTest extends TestCase
{
    protected string $html;

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->html);
    }

    public function testBaseSidebar()
    {
        $this->renderComponent(view('hyde::components.docs.sidebar'))
            ->assertDontSee('<a href="docs/index.html">')
            ->assertSeeHtml('<nav id="sidebar-navigation"')
            ->assertSeeHtml('<a href="index.html">Back to home page</a>')
            ->assertSeeHtml('<ul id="sidebar-navigation-items" role="list" class="pl-2">')
            ->assertDontSee('<li class="sidebar-navigation-item')
            ->allGood();

        $this->assertViewWasRendered(view('hyde::components.docs.sidebar-navigation', [
            'sidebar' => DocumentationSidebar::create(),
        ]));
    }

    public function testBaseSidebarWithItems()
    {
        $this->mockRoute();
        $this->file('_docs/index.md');
        $this->file('_docs/first.md');

        $this->renderComponent(view('hyde::components.docs.sidebar'))
            ->assertSeeHtml('<a href="docs/index.html">')
            ->assertSeeHtml('<nav id="sidebar-navigation"')
            ->assertSeeHtml('<ul id="sidebar-navigation-items" role="list" class="pl-2">')
            ->assertSeeHtml('<li class="sidebar-navigation-item')
            ->allGood();

        $this->assertViewWasRendered(view('hyde::components.docs.sidebar-navigation', [
            'sidebar' => DocumentationSidebar::create(),
        ]));
    }

    public function testBaseSidebarWithGroupedItems()
    {
        $this->mockRoute();
        $this->mockPage();
        $this->file('_docs/index.md');
        $this->markdown('_docs/first.md', matter: ['navigation.group' => 'Group 1']);

        $this->renderComponent(view('hyde::components.docs.sidebar'))
            ->assertSeeText('Group 1')
            ->assertSeeText('First')
            ->assertSeeHtml('href="docs/first.html"')
            ->assertSeeHtml('<ul id="sidebar-navigation-items" role="list"')
            ->assertSeeHtml('<li class="sidebar-navigation-item')
            ->assertSeeHtml('<li class="sidebar-navigation-group')
            ->assertSeeHtml('class="sidebar-navigation-group"')
            ->assertSeeHtml('class="sidebar-navigation-group-header')
            ->assertSeeHtml('class="sidebar-navigation-group-heading')
            ->assertSeeHtml('class="sidebar-navigation-group-toggle')
            ->assertSeeHtml('class="sidebar-navigation-group-toggle-icon')
            ->assertSeeHtml('class="sidebar-navigation-group-list')
            ->allGood();

        $this->assertViewWasRendered(view('hyde::components.docs.collapsible-grouped-sidebar-navigation', [
            'sidebar' => DocumentationSidebar::create(),
        ]));
    }

    public function testBaseSidebarWithNonCollapsibleGroupedItems()
    {
        $this->mockRoute();
        $this->mockPage();
        $this->file('_docs/index.md');
        $this->markdown('_docs/first.md', matter: ['navigation.group' => 'Group 1']);
        config(['docs.sidebar.collapsible' => false]);

        $this->renderComponent(view('hyde::components.docs.sidebar'))
            ->assertSeeText('Group 1')
            ->assertSeeText('First')
            ->assertSeeHtml('href="docs/first.html"')
            ->assertSeeHtml('<ul id="sidebar-navigation-items" role="list"')
            ->assertSeeHtml('<li class="sidebar-navigation-item')
            ->assertSeeHtml('<li class="sidebar-navigation-group')
            ->assertSeeHtml('class="sidebar-navigation-group"')
            ->assertSeeHtml('class="sidebar-navigation-group-header')
            ->assertSeeHtml('class="sidebar-navigation-group-heading')
            ->assertSeeHtml('class="sidebar-navigation-group-list')
            ->assertDontSee('sidebar-navigation-group-toggle')
            ->assertDontSee('sidebar-navigation-group-toggle-icon')
            ->allGood();

        $this->assertViewWasRendered(view('hyde::components.docs.grouped-sidebar-navigation', [
            'sidebar' => DocumentationSidebar::create(),
        ]));
    }

    protected function renderComponent(View $view): self
    {
        try {
            $this->html = $view->render();
            /** @noinspection LaravelFunctionsInspection */
            if (env('TEST_HTML_DEBUG', false)) {
                file_put_contents(Hyde::path('_site/test.html'), $this->html);
                echo "\e[0;32mCreated file: \e[0m".realpath(Hyde::path('_site/test.html'));
            }
        } catch (Throwable $exception) {
            /** @noinspection LaravelFunctionsInspection */
            if (env('TEST_HTML_DEBUG', false)) {
                throw $exception;
            }
            $this->fail($exception->getMessage());
        }

        $this->assertIsString($this->html);

        return $this;
    }

    protected function assertViewWasRendered(View $view): self
    {
        $this->assertStringContainsString($view->render(), $this->html);

        return $this;
    }

    protected function assertSee(string $text, bool $escape = true): self
    {
        $this->assertStringContainsString($escape ? e($text) : $text, $this->html);

        return $this;
    }

    protected function assertSeeHtml(string $text, bool $escape = false): self
    {
        $this->assertStringContainsString($escape ? e($text) : $text, $this->html);

        return $this;
    }

    protected function assertSeeText(string $text): self
    {
        $this->assertSee($text);

        return $this;
    }

    protected function assertDontSee(string $text): self
    {
        $this->assertStringNotContainsString($text, $this->html);

        return $this;
    }

    protected function allGood(): self
    {
        // Just an empty helper so we get easier Git diffs when adding new assertions.

        return $this;
    }
}
