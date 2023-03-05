<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\View\View;
use Throwable;

/**
 * Very high level test of the sidebar views and their combinations of layouts.
 */
class SidebarViewTest extends TestCase
{
    protected static bool $writeToDisk = false;
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
            if (self::$writeToDisk) {
                file_put_contents(Hyde::path('_site/test.html'), $this->html);
                echo "\e[0;32mCreated file: \e[0m" . realpath(Hyde::path('_site/test.html'));
            }
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

    protected function assertDontSee(string $text): self
    {
        $this->assertStringNotContainsString($text, $this->html);

        return $this;
    }
}
