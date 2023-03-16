<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\View\Factory;
use Mockery;

/**
 * @covers \Hyde\Framework\Views\Components\BreadcrumbsComponent
 *
 * @see \Hyde\Framework\Testing\Unit\BreadcrumbsComponentViewTest
 */
class BreadcrumbsComponentTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        app()->forgetInstances();
    }

    public function testCanConstruct()
    {
        $this->mockPage(new MarkdownPage());

        $this->assertInstanceOf(BreadcrumbsComponent::class, new BreadcrumbsComponent());
    }

    public function testCanRender()
    {
        $this->mockPage(new MarkdownPage());

        $view = Mockery::mock(\Illuminate\View\View::class);
        $mock = Mockery::mock(Factory::class);
        $mock->shouldReceive('make')->once()->with('hyde::components.breadcrumbs')->andReturn($view);
        app()->singleton('view', fn () =>$mock);
        View::swap($mock);

        $this->assertSame($view, (new BreadcrumbsComponent())->render());

        Mockery::close();
    }

    public function testCanGenerateBreadcrumbs()
    {
        $this->mockPage(new MarkdownPage());

        $this->assertIsArray((new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForIndexPage()
    {
        $this->mockPage(new MarkdownPage('index'));

        $this->assertSame(['index.html' => 'Home'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForRootPage()
    {
        $this->mockPage(new MarkdownPage('foo'));

        $this->assertSame(['index.html' => 'Home',  'foo.html' => 'Foo'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPage()
    {
        $this->mockPage(new MarkdownPage('foo/bar'));

        $this->assertSame(['../index.html' => 'Home', '../foo/index.html' => 'Foo', '../foo/bar.html' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForVeryNestedPage()
    {
        $this->mockPage(new MarkdownPage('foo/bar/baz/cat/hat'));

        $this->assertSame([
            '../../../../index.html' => 'Home',
            '../../../../foo/index.html' => 'Foo',
            '../../../../foo/bar/index.html' => 'Bar',
            '../../../../foo/bar/baz/index.html' => 'Baz',
            '../../../../foo/bar/baz/cat/index.html' => 'Cat',
            '../../../../foo/bar/baz/cat/hat.html' => 'Hat',
        ], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPageWithIndex()
    {
        $this->mockPage(new MarkdownPage('foo/bar/index'));

        $this->assertSame(['../../index.html' => 'Home', '../../foo/index.html' => 'Foo', '../../foo/bar/index.html' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForIndexPageWithPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockPage(new MarkdownPage('index'));

        $this->assertSame(['./' => 'Home'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPageWithPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockPage(new MarkdownPage('foo/bar'));

        $this->assertSame(['../' => 'Home', '../foo/' => 'Foo', '../foo/bar' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPageWithIndexWithPrettyUrls()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->mockPage(new MarkdownPage('foo/bar/index'));

        $this->assertSame(['../../' => 'Home', '../../foo/' => 'Foo', '../../foo/bar/' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testTitleGenerationWithKebabCaseUrl()
    {
        $this->mockPage(new MarkdownPage('foo-bar'));

        $this->assertSame(['index.html' => 'Home',  'foo-bar.html' => 'Foo Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testTitleGenerationWithSnakeCaseUrl()
    {
        $this->mockPage(new MarkdownPage('foo_bar'));

        $this->assertSame(['index.html' => 'Home',  'foo_bar.html' => 'Foo Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    protected function mockPage(MarkdownPage $page): void
    {
        Render::shouldReceive('getRoute')->once()->andReturn(new Route($page));
        Render::shouldReceive('getRouteKey')->andReturn($page->getOutputPath());
    }
}
