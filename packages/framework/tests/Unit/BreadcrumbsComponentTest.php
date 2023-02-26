<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Facade;
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

        app()->instance(ViewFactory::class, Mockery::mock(Factory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn($mock);
        }));

        $this->assertInstanceOf(Factory::class, (new BreadcrumbsComponent())->render());
    }

    public function testCanGenerateBreadcrumbs()
    {
        $this->mockPage(new MarkdownPage());

        $this->assertIsArray((new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForIndexPage()
    {
        $this->mockPage(new MarkdownPage('index'));

        $this->assertSame(['/' => 'Home'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForRootPage()
    {
        $this->mockPage(new MarkdownPage('foo'));

        $this->assertSame(['/' => 'Home',  'foo' => 'Foo'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPage()
    {
        $this->mockPage(new MarkdownPage('foo/bar'));

        $this->assertSame(['/' => 'Home', '../foo/' => 'Foo', '../foo/bar' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPageWithIndex()
    {
        $this->mockPage(new MarkdownPage('foo/bar/index'));

        $this->assertSame(['/' => 'Home', '../../foo/' => 'Foo', '../../foo/bar/' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    protected function mockPage(MarkdownPage $page): void
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route($page));
        Render::shouldReceive('getCurrentPage')->andReturn($page->getOutputPath());
    }
}
