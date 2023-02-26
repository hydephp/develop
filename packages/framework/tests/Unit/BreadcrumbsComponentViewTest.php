<?php

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

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                &nbsp;&gt;&gt;&nbsp;
                Foo
            </nav>
        HTML;

        $this->assertRenderedMatchesExpected($expected);
    }

    public function testRenderedBladeViewOnIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('index')));

        $this->assertSame('', Blade::renderComponent(new BreadcrumbsComponent()));
    }

    public function testRenderedBladeViewOnNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar')));

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                &nbsp;&gt;&gt;&nbsp;
                <a href="foo/" class="hover:underline">Foo</a>
                &nbsp;&gt;&gt;&nbsp;
                Bar
            </nav>
        HTML;

        $this->assertRenderedMatchesExpected($expected);
    }

    public function testRenderedBladeViewOnDeeplyNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar/baz')));

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                &nbsp;&gt;&gt;&nbsp;
                <a href="foo/" class="hover:underline">Foo</a>
                &nbsp;&gt;&gt;&nbsp;
                <a href="foo/bar/" class="hover:underline">Bar</a>
                &nbsp;&gt;&gt;&nbsp;
                Baz
            </nav>
        HTML;

        $this->assertRenderedMatchesExpected($expected);
    }

    public function testRenderedBladeViewOnNestedIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/index')));

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                &nbsp;&gt;&gt;&nbsp;
                Foo
            </nav>
        HTML;

        $this->assertRenderedMatchesExpected($expected);
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
