<?php

/** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Facades\Blade;
use Hyde\Framework\Views\Components\BreadcrumbsComponent;

/**
 * @see \Hyde\Framework\Testing\Unit\BreadcrumbsComponentTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Views\Components\BreadcrumbsComponent::class)]
class BreadcrumbsComponentViewTest extends TestCase
{
    public function testRenderedBladeView()
    {
        $this->mockRenderPage(new MarkdownPage('foo'));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="index.html" class="hover:underline">Home</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="foo.html" aria-current="page">Foo</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnIndexPage()
    {
        $this->mockRenderPage(new MarkdownPage('index'));

        $this->assertSame('', Blade::renderComponent(new BreadcrumbsComponent()));
    }

    public function testRenderedBladeViewOnNestedPage()
    {
        $this->mockRenderPage(new MarkdownPage('foo/bar'));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="../index.html" class="hover:underline">Home</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../foo/index.html" class="hover:underline">Foo</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../foo/bar.html" aria-current="page">Bar</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnDeeplyNestedPage()
    {
        $this->mockRenderPage(new MarkdownPage('foo/bar/baz'));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="../../index.html" class="hover:underline">Home</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../../foo/index.html" class="hover:underline">Foo</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../../foo/bar/index.html" class="hover:underline">Bar</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../../foo/bar/baz.html" aria-current="page">Baz</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewOnNestedIndexPage()
    {
        $this->mockRenderPage(new MarkdownPage('foo/index'));

        $this->assertRenderedMatchesExpected(<<<'HTML'
            <nav aria-label="breadcrumb">
                <ol class="flex">
                    <li>
                        <a href="../index.html" class="hover:underline">Home</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href="../foo/index.html" aria-current="page">Foo</a>
                    </li>
                </ol>
            </nav>
        HTML);
    }

    public function testRenderedBladeViewWithAttributes()
    {
        $this->mockRenderPage(new MarkdownPage());

        $html = Blade::renderComponent((new BreadcrumbsComponent())->withAttributes(['class' => 'foo']));

        $expected = <<<'HTML'
            <nav aria-label="breadcrumb" class="foo">
                <ol class="flex">
                    <li>
                        <a href="index.html" class="hover:underline">Home</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    </li>
                    <li>
                        <a href=".html" aria-current="page"></a>
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

    protected function mockRenderPage(MarkdownPage $page): void
    {
        Render::setPage($page);
    }
}
