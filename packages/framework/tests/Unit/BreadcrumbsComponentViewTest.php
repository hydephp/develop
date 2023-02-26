<?php /** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @covers \Hyde\Framework\Views\Components\BreadcrumbsComponent
 *
 * @see \Hyde\Framework\Testing\Unit\BreadcrumbsComponentTest
 */
class BreadcrumbsComponentViewTest extends TestCase
{
    public function testRenderedBladeView()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol>
                    <a href="/" class="hover:underline">Home</a>
                    &gt;
                    <a href="foo" aria-current="page">Foo</a>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('index')));

        $this->assertSame('', Blade::renderComponent(new BreadcrumbsComponent()));
    }

    public function testRenderedBladeViewOnNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol>
                    <a href="/" class="hover:underline">Home</a>
                    &gt;
                    <a href="foo/" class="hover:underline">Foo</a>
                    &gt;
                    <a href="foo/bar" aria-current="page">Bar</a>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnDeeplyNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar/baz')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol>
                    <a href="/" class="hover:underline">Home</a>
                    &gt;
                    <a href="foo/" class="hover:underline">Foo</a>
                    &gt;
                    <a href="foo/bar/" class="hover:underline">Bar</a>
                    &gt;
                    <a href="foo/bar/baz" aria-current="page">Baz</a>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnNestedIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/index')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol>
                    <a href="/" class="hover:underline">Home</a>
                    &gt;
                    <a href="foo/" aria-current="page">Foo</a>
                </ol>
            </nav>
        HTML);
    }

    protected function assertRenderedMatchesExpected(string $expected): void
    {
        $html = Blade::renderComponent(new BreadcrumbsComponent());

        $this->assertSame($this->stripIndentation($expected), $this->stripIndentation($html));
    }

    protected function stripIndentation(string $string): string
    {
        return implode("\n", array_filter(array_map(fn ($line) => ltrim($line), explode("\n", $string))));
    }
}
