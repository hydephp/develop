<?php

/** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

use function tap;

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
                <ol class="flex">
                    <li>
                        <a href="/" class="hover:underline">Home</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo" aria-current="page">Foo</a>
                    </li>
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
                <ol class="flex">
                    <li>
                        <a href="/" class="hover:underline">Home</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/" class="hover:underline">Foo</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/bar" aria-current="page">Bar</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnDeeplyNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar/baz')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="/" class="hover:underline">Home</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/" class="hover:underline">Foo</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/bar/" class="hover:underline">Bar</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/bar/baz" aria-current="page">Baz</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnNestedIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/index')));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="/" class="hover:underline">Home</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="foo/" aria-current="page">Foo</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewWithAttributes()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        $html = Blade::renderComponent((new BreadcrumbsComponent())->withAttributes(['class' => 'foo']));

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb" class="foo">
                <ol class="flex">
                    <li>
                        <a href="/" class="hover:underline">Home</a>
                    </li>
                    <span class="px-1" aria-hidden="true">&gt;</span>
                    <li>
                        <a href="" aria-current="page"></a>
                    </li>
                </ol>
            </nav>
        HTML;

        $this->assertSame($this->stripIndentation($expected), $this->stripIndentation($html));
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
