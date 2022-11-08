<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Features;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Facades\Features::darkmode
 * @covers \Hyde\Facades\Features::hasDarkmode
 */
class DarkmodeFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRoute();
        $this->mockPage();
    }

    public function test_has_darkmode()
    {
        Config::set('hyde.features', []);

        $this->assertFalse(Features::hasDarkmode());

        Config::set('hyde.features', [
            Features::darkmode(),
        ]);

        $this->assertTrue(Features::hasDarkmode());
    }

    public function test_layout_has_toggle_button_and_script_when_enabled()
    {
        Config::set('hyde.features', [
            Features::markdownPages(),
            Features::bladePages(),
            Features::darkmode(),
        ]);

        $view = view('hyde::layouts/page')->with([
            'title' => 'foo',
            'content' => 'foo',
            'currentPage' => 'foo',
        ])->render();

        $this->assertStringContainsString('title="Toggle theme"', $view);
        $this->assertStringContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function test_documentation_page_has_toggle_button_and_script_when_enabled()
    {
        Config::set('hyde.features', [
            Features::documentationPages(),
            Features::darkmode(),
        ]);

        view()->share('page', new DocumentationPage());

        $view = view('hyde::layouts/docs')->with([
            'title' => 'foo',
            'content' => 'foo',
            'currentPage' => 'foo',
        ])->render();

        $this->assertStringContainsString('title="Toggle theme"', $view);
        $this->assertStringContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function test_dark_mode_theme_button_is_hidden_in_layouts_when_disabled()
    {
        Config::set('hyde.features', [
            Features::markdownPages(),
            Features::bladePages(),
        ]);

        $view = view('hyde::layouts/page')->with([
            'title' => 'foo',
            'content' => 'foo',
            'currentPage' => 'foo',
        ])->render();

        $this->assertStringNotContainsString('title="Toggle theme"', $view);
        $this->assertStringNotContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function test_dark_mode_theme_button_is_hidden_in_documentation_pages_when_disabled()
    {
        Config::set('hyde.features', [
            Features::documentationPages(),
        ]);

        view()->share('page', new DocumentationPage());

        $view = view('hyde::layouts/docs')->with([
            'title' => 'foo',
            'content' => 'foo',
            'currentPage' => 'foo',
        ])->render();

        $this->assertStringNotContainsString('title="Toggle theme"', $view);
        $this->assertStringNotContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }
}
