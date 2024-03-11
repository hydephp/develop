<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use DOMXPath;
use Hyde\Hyde;
use DOMDocument;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\InMemoryPage;
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
        $this->menu()
            ->assertHasId('main-navigation')
            ->assertHasElement('theme-toggle-button')
            ->assertHasElement('navigation-toggle-button')
            ->assertHasElement('main-navigation-links')
            ->assertHasNoPages()
            ->finish();
    }

    public function testDocumentationSidebarMenu()
    {
        $this->sidebar()
            ->assertHasId('sidebar')
            ->assertHasElement('sidebar-header')
            ->assertHasElement('sidebar-brand')
            ->assertHasElement('sidebar-navigation')
            ->assertHasElement('sidebar-footer')
            ->assertHasElement('theme-toggle-button')
            ->assertHasElement('sidebar-items')
            ->assertHasNoPages()
            ->finish();
    }

    public function testNavigationMenuWithPages()
    {
        $this->menu($this->withExamplePages())
            ->assertHasPages([
                'index.html' => 'Home',
                'first.html' => 'First',
                'about.html' => 'About',
                'custom.html' => 'Label',
            ])
            ->finish();
    }

    public function testNavigationMenuFromNestedRoute()
    {
        $this->fromPage('nested/page')
            ->menu($this->withExamplePages())
            ->assertHasPages([
                '../index.html' => 'Home',
                '../first.html' => 'First',
                '../about.html' => 'About',
                '../custom.html' => 'Label',
            ])
            ->finish();
    }

    protected function withPages(array $pages): static
    {
        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    protected function fromPage(string $mockedRoute): static
    {
        $this->mockCurrentPage($mockedRoute);

        return $this;
    }

    protected function menu(array $withPages = []): RenderedMainNavigationMenu
    {
        $this->withPages($withPages);

        app()->instance('navigation.main', NavigationMenuGenerator::handle(MainNavigationMenu::class));

        return new RenderedMainNavigationMenu($this, $this->render('hyde::layouts.navigation'));
    }

    protected function sidebar(array $withPages = []): RenderedDocumentationSidebarMenu
    {
        $this->withPages($withPages);

        app()->instance('navigation.sidebar', NavigationMenuGenerator::handle(DocumentationSidebar::class));

        return new RenderedDocumentationSidebarMenu($this, $this->render('hyde::components.docs.sidebar'));
    }

    protected function render(string $view): string
    {
        return view($view)->render();
    }

    protected function withExamplePages(): array
    {
        return [
            new MarkdownPage('index'),
            new MarkdownPage('404'),
            new BladePage('about'),
            new BladePage('hidden', ['navigation.hidden' => true]),
            new InMemoryPage('custom', ['navigation.label' => 'Label']),
            new HtmlPage('first', ['navigation.priority' => 1]),
        ];
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

    public function finish(): void
    {
        // Empty method to provide cleaner diffs when using method chaining.
    }

    public function assertHasId(string $id): static
    {
        $node = $this->ast->documentElement;

        $this->test->assertTrue($node->hasAttribute('id'));
        $this->test->assertSame($id, $node->getAttribute('id'));

        return $this;
    }

    public function assertHasElement(string $id): static
    {
        $element = $this->ast->getElementById($id);

        if ($element === null) {
            // Search for the element in the entire HTML.
            $xpath = new DOMXPath($this->ast);
            $element = $xpath->query("//*[@id='$id']")->item(0);

            if ($element === null) {
                // See if there is an element containing the ID as a class.
                $element = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $id ')]")->item(0);
            }
        }

        $this->test->assertNotNull($element, "Element with '$id' not found in the HTML.");

        return $this;
    }

    public function assertHasPages(array $pages): static
    {
        $renderedPages = $this->getRenderedPages();

        $this->test->assertSame($pages, $renderedPages, sprintf('Rendered pages do not match expected pages: %s', json_encode([
            'expected' => $pages,
            'rendered' => $renderedPages,
        ], JSON_PRETTY_PRINT)));

        return $this;
    }

    public function assertHasNoPages(): static
    {
        $this->test->assertEmpty($this->getRenderedPages());

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

    /** @return array<string, string> */
    protected function getRenderedPages(): array
    {
        $containerNodeId = static::TYPE === MainNavigationMenu::class ? 'main-navigation-links' : 'sidebar-items';

        $this->assertHasElement($containerNodeId);

        $containerNode = $this->ast->getElementById($containerNodeId);

        $links = $containerNode->getElementsByTagName('a');

        $pages = [];

        foreach ($links as $link) {
            $pages[$link->getAttribute('href')] = $link->textContent;
        }

        return $pages;
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
