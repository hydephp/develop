<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\CreatesApplication;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Factory;
use Mockery;

/**
 * @covers \Hyde\Framework\Views\Components\BreadcrumbsComponent
 */
class BreadcrumbsComponentTest extends UnitTestCase
{
    use CreatesApplication;

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
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        $this->assertInstanceOf(BreadcrumbsComponent::class, new BreadcrumbsComponent());
    }

    public function testCanRender()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        app()->instance(ViewFactory::class, Mockery::mock(Factory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn($mock);
        }));

        $this->assertInstanceOf(Factory::class, (new BreadcrumbsComponent())->render());
    }

    public function testCanGenerateBreadcrumbs()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        $this->assertIsArray((new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForIndexPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('index')));

        $this->assertSame(['/' => 'Home'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForRootPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo')));

        $this->assertSame(['/' => 'Home',  'foo' => 'Foo'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPage()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar')));

        $this->assertSame(['/' => 'Home', 'foo/' => 'Foo', 'foo/bar' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testCanGenerateBreadcrumbsForNestedPageWithIndex()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo/bar/index')));

        $this->assertSame(['/' => 'Home', 'foo/' => 'Foo', 'foo/bar/' => 'Bar'], (new BreadcrumbsComponent())->breadcrumbs);
    }

    public function testRenderedBladeView()
    {
        $this->createApplication();

        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage('foo')));

        $html = Blade::renderComponent(new BreadcrumbsComponent());

        $expected = [
            '<nav aria-label="breadcrumb">',
        ];

        foreach ($expected as $string) {
            $this->assertStringContainsString($string, $html);
        }
    }
}
