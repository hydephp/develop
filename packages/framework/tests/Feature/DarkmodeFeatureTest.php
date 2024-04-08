<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Features;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;

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

    public function testHasDarkmodeIsFalseWhenNotSet()
    {
        Config::set('hyde.features', []);

        $this->assertFalse(Features::hasDarkmode());
    }

    public function testHasDarkmodeIsTrueWhenSet()
    {
        Config::set('hyde.features', [
            Features::darkmode(),
        ]);

        $this->assertTrue(Features::hasDarkmode());
    }

    public function testLayoutHasToggleButtonAndScriptWhenEnabled()
    {
        Config::set('hyde.features', [
            Features::markdownPages(),
            Features::bladePages(),
            Features::darkmode(),
        ]);

        app()->instance('navigation.main', new MainNavigationMenu());

        $view = view('hyde::layouts/page')->with([
            'title' => 'foo',
            'content' => 'foo',
            'routeKey' => 'foo',
        ])->render();

        $this->assertStringContainsString('title="Toggle theme"', $view);
        $this->assertStringContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function testDocumentationPageHasToggleButtonAndScriptWhenEnabled()
    {
        Config::set('hyde.features', [
            Features::documentationPages(),
            Features::darkmode(),
        ]);

        view()->share('page', new DocumentationPage());

        $view = view('hyde::layouts/docs')->with([
            'title' => 'foo',
            'content' => 'foo',
            'routeKey' => 'foo',
        ])->render();

        $this->assertStringContainsString('title="Toggle theme"', $view);
        $this->assertStringContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function testDarkModeThemeButtonIsHiddenInLayoutsWhenDisabled()
    {
        Config::set('hyde.features', [
            Features::markdownPages(),
            Features::bladePages(),
        ]);

        Hyde::boot();

        $view = view('hyde::layouts/page')->with([
            'title' => 'foo',
            'content' => 'foo',
            'routeKey' => 'foo',
        ])->render();

        $this->assertStringNotContainsString('title="Toggle theme"', $view);
        $this->assertStringNotContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }

    public function testDarkModeThemeButtonIsHiddenInDocumentationPagesWhenDisabled()
    {
        Hyde::boot();

        Config::set('hyde.features', [
            Features::documentationPages(),
        ]);

        $view = view('hyde::layouts/docs')->with([
            'title' => 'foo',
            'content' => 'foo',
            'routeKey' => 'foo',
        ])->render();

        $this->assertStringNotContainsString('title="Toggle theme"', $view);
        $this->assertStringNotContainsString('<script>if (localStorage.getItem(\'color-theme\') === \'dark\'', $view);
    }
}
