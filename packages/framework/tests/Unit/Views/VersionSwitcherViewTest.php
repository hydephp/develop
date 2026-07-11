<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestsBladeViews;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\Support\TestView;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

/**
 * @see resources/views/components/docs/version-switcher.blade.php
 */
class VersionSwitcherViewTest extends TestCase
{
    use TestsBladeViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRoute();
    }

    protected function renderComponent(?string $version = null): TestView
    {
        return $this->view(view('hyde::components.docs.version-switcher', [
            'sidebar' => NavigationMenuGenerator::handle(DocumentationSidebar::class, $version ? DocumentationVersions::get($version) : null),
        ]));
    }

    public function testComponentIsNotRenderedWhenVersioningIsDisabled()
    {
        $this->renderComponent()->assertDontSee('docs-version-switcher');
    }

    public function testComponentIsNotRenderedWhenOnlyOneVersionIsConfigured()
    {
        config(['docs.versions' => ['2.x']]);

        $this->renderComponent('2.x')->assertDontSee('docs-version-switcher');
    }

    public function testComponentRendersVersionsWhenVersioningIsEnabled()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/installation')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/installation')));

        $this->mockPage(new DocumentationPage('2.x/installation'), 'docs/2.x/installation');

        $view = $this->renderComponent('2.x');

        $view->assertHasElement('#docs-version-switcher');
        $view->assertSee('Version 2.x');
        $view->assertSee('../../docs/1.x/installation.html');
    }

    public function testComponentLinksToVersionHomeWhenEquivalentPageIsMissing()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/index')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/upgrading')));

        $this->mockPage(new DocumentationPage('2.x/upgrading'), 'docs/2.x/upgrading');

        $view = $this->renderComponent('2.x');

        $view->assertSee('../../docs/1.x/index.html');
    }

    public function testComponentLinksToVersionHomeWhenThereIsNoCurrentPage()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Render::clearData();
        $this->mockRoute();

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/index')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/index')));

        $view = $this->renderComponent('2.x');

        $view->assertHasElement('#docs-version-switcher');
        $view->assertSee('docs/1.x/index.html');
    }

    public function testComponentRendersDisabledVersionWhenNoEquivalentPageOrHomeExists()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/upgrading')));

        $this->mockPage(new DocumentationPage('2.x/upgrading'), 'docs/2.x/upgrading');

        $this->renderComponent('2.x')->assertSee('<span aria-disabled="true" class="block py-1 px-3 opacity-50">1.x</span>', false);
    }

    public function testComponentMarksTheCurrentVersionAsSelected()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/installation')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/installation')));

        $this->mockPage(new DocumentationPage('2.x/installation'), 'docs/2.x/installation');

        $this->renderComponent('2.x')->assertSeeOnce('aria-current="page"');
    }

    public function testComponentDoesNotUseListboxSemantics()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/installation')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/installation')));

        $this->mockPage(new DocumentationPage('2.x/installation'), 'docs/2.x/installation');

        $view = $this->renderComponent('2.x');

        $view->assertDontSee('role="listbox"', false);
        $view->assertDontSee('role="option"', false);
        $view->assertDontSee('aria-haspopup', false);
        $view->assertDontSee('aria-selected', false);
    }

    public function testEscapeKeyClosesTheSwitcherAndReturnsFocusToTheButton()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::routes()->addRoute(new Route(new DocumentationPage('1.x/installation')));
        Hyde::routes()->addRoute(new Route(new DocumentationPage('2.x/installation')));

        $this->mockPage(new DocumentationPage('2.x/installation'), 'docs/2.x/installation');

        $view = $this->renderComponent('2.x');

        $view->assertSee('@keydown.escape.window="versionSwitcherOpen = false; $refs.versionSwitcherButton.focus()"', false);
    }

    public function testSwitcherListIsScrollableSoALongListOfVersionsRemainsUsable()
    {
        config(['docs.versions' => ['1.x', '2.x', '3.x', '4.x', '5.x', '6.x', '7.x', '8.x', '9.x', '10.x']]);

        foreach (config('docs.versions') as $version) {
            Hyde::routes()->addRoute(new Route(new DocumentationPage("$version/installation")));
        }

        $this->mockPage(new DocumentationPage('10.x/installation'), 'docs/10.x/installation');

        $view = $this->renderComponent('10.x');

        $view->assertSee('max-h-64 overflow-y-auto', false);

        foreach (config('docs.versions') as $version) {
            $view->assertSee($version);
        }
    }
}
