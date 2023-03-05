<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Illuminate\Contracts\View\View;
use Throwable;

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
            ->assertSee('sidebar')
            ->assertSeeText('sidebar')
            ->assertSeeHtml('sidebar');
    }

    protected function renderComponent(View $view): self
    {
        try {
            $this->html = $view->render();
        } catch (Throwable $exception) {
            $this->fail($exception->getMessage());
        }

        $this->assertIsString($this->html);

        return $this;
    }

    protected function assertSee(string $text, bool $escape = true): self
    {
        $this->assertStringContainsString($escape ? e($text) : false, $this->html);

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
