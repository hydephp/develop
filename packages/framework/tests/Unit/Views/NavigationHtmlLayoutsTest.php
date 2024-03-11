<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestCase;
use Hyde\Foundation\HydeKernel;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * Very high level tests for navigation menu and sidebar view layouts.
 *
 * @see \Hyde\Framework\Testing\Feature\AutomaticNavigationConfigurationsTest
 */
class NavigationHtmlLayoutsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new TestKernel();
        HydeKernel::setInstance($this->kernel);

        app()->instance('navigation.main', null);
        app()->instance('navigation.sidebar', null);

        $this->mockPage();
        $this->mockRoute();
    }

    public function testMainNavigationMenu()
    {
        $this->menu()->assertTrue();
    }

    public function testDocumentationSidebarMenu()
    {
        $this->sidebar()->assertTrue();
    }

    protected function withPages(array $pages): static
    {
        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    protected function menu(?array $withPages = null): RenderedNavigationMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        $menu = NavigationMenuGenerator::handle(MainNavigationMenu::class);
        app()->instance('navigation.main', $menu);

        return new RenderedNavigationMenu($this, $this->render('hyde::layouts.navigation'));
    }

    protected function sidebar(?array $withPages = null): RenderedNavigationMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        app()->instance('navigation.sidebar', $menu);

        return new RenderedNavigationMenu($this, $this->render('hyde::components.docs.sidebar'));
    }

    protected function render(string $view): string
    {
        return view($view)->render();
    }
}

class RenderedNavigationMenu
{
    protected NavigationHtmlLayoutsTest $test;
    protected string $html;

    public function __construct(NavigationHtmlLayoutsTest $test, string $html)
    {
        $this->test = $test;
        $this->html = $html;
    }

    public function assertTrue(): void
    {
        $this->test->assertTrue(true);
    }
}

class TestKernel extends HydeKernel
{
    protected ?RouteCollection $mockRoutes = null;

    public function setRoutes(Collection $routes): void
    {
        $this->mockRoutes = RouteCollection::make($routes);
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    public function routes(): RouteCollection
    {
        return $this->mockRoutes ?? parent::routes();
    }
}
