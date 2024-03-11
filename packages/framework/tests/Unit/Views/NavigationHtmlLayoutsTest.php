<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use DOMDocument;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Hyde\Foundation\HydeKernel;
use JetBrains\PhpStorm\NoReturn;
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
        $this->menu()->assertHasId('main-navigation');
    }

    public function testDocumentationSidebarMenu()
    {
        $this->sidebar()->assertHasId('sidebar');
    }

    protected function withPages(array $pages): static
    {
        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    protected function menu(?array $withPages = null): RenderedMainNavigationMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        $menu = NavigationMenuGenerator::handle(MainNavigationMenu::class);
        app()->instance('navigation.main', $menu);

        return new RenderedMainNavigationMenu($this, $this->render('hyde::layouts.navigation'));
    }

    protected function sidebar(?array $withPages = null): RenderedDocumentationSidebarMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        app()->instance('navigation.sidebar', $menu);

        return new RenderedDocumentationSidebarMenu($this, $this->render('hyde::components.docs.sidebar'));
    }

    protected function render(string $view): string
    {
        return view($view)->render();
    }
}

abstract class RenderedNavigationMenu
{
    protected readonly NavigationHtmlLayoutsTest $test;
    protected readonly string $html;
    protected readonly DOMDocument $ast;

    protected const TYPE = null;

    public function __construct(NavigationHtmlLayoutsTest $test, string $html)
    {
        $this->test = $test;
        $this->html = $html;

        $this->ast = $this->parseHtml();

        $this->test->assertNotEmpty($this->html);
    }

    public function assertTrue(): void
    {
        $this->test->assertTrue(true);
    }

    public function assertHasId(string $id): static
    {
        $node = $this->ast->documentElement;

        $this->test->assertTrue($node->hasAttribute('id'));
        $this->test->assertSame($id, $node->getAttribute('id'));

        return $this;
    }

    #[NoReturn]
    public function dd(bool $writeHtml = true): void
    {
        if ($writeHtml) {
            file_put_contents(Hyde::path(Str::kebab(class_basename(static::TYPE)).'.html'), $this->html);
        }

        exit(trim($this->html)."\n\n");
    }

    protected function parseHtml(): DOMDocument
    {
        $dom = new DOMDocument();

        $dom->loadHTML($this->html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_PARSEHUGE);

        return $dom;
    }
}

class RenderedMainNavigationMenu extends RenderedNavigationMenu
{
    protected const TYPE = MainNavigationMenu::class;
}

class RenderedDocumentationSidebarMenu extends RenderedNavigationMenu
{
    protected const TYPE = DocumentationSidebar::class;
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
