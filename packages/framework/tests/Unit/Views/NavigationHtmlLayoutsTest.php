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

use function app;
use function md5;
use function view;
use function trim;
use function range;
use function config;
use function collect;
use function sprintf;
use function explode;
use function implode;
use function array_map;
use function strip_tags;
use function array_keys;
use function json_encode;
use function str_replace;
use function preg_replace;
use function substr_count;
use function array_filter;
use function array_values;
use function class_basename;
use function file_put_contents;

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
            ->assertDoesNotHaveElement('dropdown')
            ->assertDoesNotHaveElement('dropdown-items')
            ->assertDoesNotHaveElement('dropdown-button')
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
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - First
                - About
                - Label
                HTML
            );
    }

    public function testNavigationMenuWithDropdownPages()
    {
        $this->useSubdirectoryConfig('dropdown')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertHasDropdowns(['Foo' => ['Bar', 'Baz']])
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                    - Bar
                    - Baz
                HTML
            );
    }

    public function testNavigationMenuWithFlatSubdirectoryPages()
    {
        $this->useSubdirectoryConfig('flat')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertDoesNotHaveDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Bar
                - Baz
                HTML
            );
    }

    public function testNavigationMenuWithHiddenSubdirectoryPages()
    {
        $this->useSubdirectoryConfig('hidden')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertDoesNotHaveDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
            ])
            ->assertItemsLookLike('- Home');
    }

    protected function withPages(array $pages): static
    {
        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    public function testNavigationMenuWithFlatSubdirectoryPagesAndMatchingSubdirectoryPage()
    {
        $this->useSubdirectoryConfig('flat')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertDoesNotHaveDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo.html' => 'Foo',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                - Bar
                - Baz
                HTML
            );
    }

    public function testNavigationMenuWithFlatSubdirectoryPagesAndMatchingSubdirectoryIndexPage()
    {
        $this->useSubdirectoryConfig('flat')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo/index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertDoesNotHaveDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/index.html' => 'Foo',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                - Bar
                - Baz
                HTML
            );
    }

    public function testNavigationMenuWithFlatSubdirectoryPagesAndMatchingSubdirectoryAndIndexPage()
    {
        $this->useSubdirectoryConfig('flat')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo'),
                new MarkdownPage('foo/index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertDoesNotHaveDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo.html' => 'Foo',
                'foo/index.html' => 'Foo',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                - Foo
                - Bar
                - Baz
                HTML
            );
    }

    public function testNavigationMenuWithDropdownSubdirectoryPagesAndMatchingSubdirectoryPage()
    {
        $this->useSubdirectoryConfig('dropdown')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertHasDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                    - Bar
                    - Baz
                HTML
            );
    }

    public function testNavigationMenuWithDropdownSubdirectoryPagesAndMatchingSubdirectoryIndexPage()
    {
        $this->useSubdirectoryConfig('dropdown')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo/index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertHasDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/index.html' => 'Foo',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                    - Foo
                    - Bar
                    - Baz
                HTML
            );
    }

    public function testNavigationMenuWithDropdownSubdirectoryPagesAndMatchingSubdirectoryAndIndexPage()
    {
        $this->useSubdirectoryConfig('dropdown')
            ->menu([
                new MarkdownPage('index'),
                new MarkdownPage('foo'),
                new MarkdownPage('foo/index'),
                new MarkdownPage('foo/bar'),
                new MarkdownPage('foo/baz'),
            ])
            ->assertHasDropdowns()
            ->assertHasPages([
                'index.html' => 'Home',
                'foo/index.html' => 'Foo',
                'foo/bar.html' => 'Bar',
                'foo/baz.html' => 'Baz',
            ])
            ->assertItemsLookLike(<<<'HTML'
                - Home
                - Foo
                    - Foo
                    - Bar
                    - Baz
                HTML
            );
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

    protected function useSubdirectoryConfig(string $option): static
    {
        config(['hyde.navigation.subdirectories' => $option]);

        return $this;
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

    public function assertDoesNotHaveElement(string $id): static
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

        $this->test->assertNull($element, "Element with '$id' found in the HTML.");

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

    public function assertLooksLike(string $expected, bool $skipHeaderRow = false): static
    {
        $actual = $this->makeTextRepresentation();

        if ($skipHeaderRow) {
            $actual = trim(Str::after($actual, "\n"));
        }
        // If expected omitted dashes, remove them from actual
        if (! str_contains($expected, '- ')) {
            $actual = str_replace('- ', '', $actual);
        }
        // If expected uses 4 spaces, replace with 2 spaces
        $expected = str_replace('    ', '  ', $expected);

        $this->test->assertSame($expected, $actual, sprintf("Actual HTML does not match expected text:\nExpected:\n%s\nActual:\n%s", $expected, $actual));

        return $this;
    }

    public function assertItemsLookLike(string $expected): static
    {
        return $this->assertLooksLike($expected, true);
    }

    public function assertHasDropdowns(?array $expected = null): static
    {
        $this->assertHasElement('dropdown');
        $this->assertHasElement('dropdown-items');
        $this->assertHasElement('dropdown-button');

        if ($expected) {
            $xpath = new DOMXPath($this->ast);
            $dropdownItems = $xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " dropdown-items ")]');

            /** @var array<string, array<string> $actual */
            $actual = [];

            foreach ($dropdownItems as $dropdownItem) {
                $button = $xpath->query('../preceding-sibling::button', $dropdownItem)->item(0);

                $label = trim($button->textContent);

                $children = array_values(array_filter(array_map('trim', explode("\n", $dropdownItem->textContent))));

                $actual[$label] = $children;
            }

            // If expected is a list array, then remove values from actual
            if (array_values($expected) === $expected) {
                $actual = array_keys($actual);
            }

            $this->test->assertSame($expected, $actual, sprintf('Rendered dropdown does not match expected format: %s', json_encode([
                'expected' => $expected,
                'rendered' => $actual,
            ], JSON_PRETTY_PRINT)));
        }

        return $this;
    }

    public function assertDoesNotHaveDropdowns(): static
    {
        $this->assertDoesNotHaveElement('dropdown');
        $this->assertDoesNotHaveElement('dropdown-items');
        $this->assertDoesNotHaveElement('dropdown-button');

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

    protected function makeTextRepresentation(): string
    {
        $html = str_replace(['<title>Open Menu</title>', '<title>Close Menu</title>'], ['Toggle menu', ''], $this->html);
        $html = str_replace(['<li', '</li>'], ['<li-keep', '</li-keep>'], $html);
        $html = str_replace(['<ul', '</ul>'], ['<ul-keep', '</ul-keep>'], $html);
        $html = strip_tags($html, '<li-keep><ul-keep>');
        $html = str_replace(['<li-keep', '</li-keep>'], ['<li', '</li>'], $html);
        $html = str_replace(['<ul-keep', '</ul-keep>'], ['<ul', '</ul>'], $html);
        $html = preg_replace('/<li[^>]*>/', '<li>', $html);
        $html = preg_replace('/<ul[^>]*>/', '<ul>', $html);
        $html = str_replace(['</li>'], '<br>', $html);
        $html = preg_replace('/\s+/', '', $html);
        $html = str_replace('<br>', "\n", $html);
        $html = str_replace('<li>', '- ', $html);
        $html = Str::replaceFirst('<ul>', "\n", $html);

        $innerUlCount = substr_count($html, '<ul>');
        if ($innerUlCount >= 1) {
            // Indent each line within the remaining UL elements
            foreach (range(1, $innerUlCount) as $item) {
                $html = Str::replaceFirst('</ul>', '</ul-first>', $html);
                $innerUlContent = Str::between($html, '<ul>', '</ul-first>');
                $id = md5("$innerUlContent-$item");
                $html = Str::replaceFirst($innerUlContent, $id, $html);
                $innerUlContent = trim(str_replace("\n", "\n  ", $innerUlContent));

                $html = str_replace("<ul>$id</ul-first>", "\n  $innerUlContent", $html);
            }
        }

        $html = Str::replaceLast('</ul>', '', $html);

        return trim(implode("\n", array_map('rtrim', explode("\n", $html))));
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
