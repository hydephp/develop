<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\View\View;

/**
 * Very high level test of the sidebar views and their combinations of layouts.
 */
class SidebarViewTest extends TestCase
{
    protected string $html;

    protected function tearDown(): void
    {
        parent::setUp();

         unset($this->html);
    }

    public function testBaseSidebar()
    {
        $this->renderComponent(view('hyde::components.docs.sidebar'))
            ->assertViewWasRendered()
            ->assertSee('sidebar')
            ->assertSeeText('sidebar')
            ->assertSeeHtml('sidebar');
    }

    protected function renderComponent(View $view): self
    {
        $this->html = $view->render();

        return $this;
    }

    protected function assertViewWasRendered(): self
    {
        $this->assertNotNull($this->html);

        return $this;
    }

    protected function assertSee(string $text, bool $escape = true): self
    {
        $this->assertStringContainsString($escape ? e($text) :false, $this->html);

        return $this;
    }

    protected function assertSeeHtml(string $text, bool $escape = true): self
    {
        $this->assertStringContainsString($escape ? e($text) : false, $this->html);

        return $this;
    }

    protected function assertSeeText(string $text): self
    {
        $this->assertSee($text);

        return $this;
    }
}
