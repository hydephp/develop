<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Support\Models\Route
 */
class RouteTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();
        Render::swap(new RenderData());
    }

    public function testConstructorCreatesRouteFromPageModel()
    {
        $this->assertInstanceOf(Route::class, new Route(new MarkdownPage()));
    }

    public function testGetPageTypeReturnsFullyQualifiedClassName()
    {
        $this->assertSame(MarkdownPage::class, (new Route(new MarkdownPage()))->getPageClass());
    }

    public function testGetSourceModelReturnsPageModel()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(MarkdownPage::class, $route->getPage());
        $this->assertSame($page, $route->getPage());
    }

    public function testGetRouteKeyReturnsPagePath()
    {
        $page = new MarkdownPage();
        $this->assertSame($page->getRouteKey(), (new Route($page))->getRouteKey());
    }

    public function testGetSourceFilePathReturnsPageSourcePath()
    {
        $page = new MarkdownPage();
        $this->assertSame($page->getSourcePath(), (new Route($page))->getSourcePath());
    }

    public function testGetOutputFilePathReturnsPageOutputPath()
    {
        $page = new MarkdownPage();
        $this->assertSame($page->getOutputPath(), (new Route($page))->getOutputPath());
    }

    public function testGetLinkReturnsCorrectPathForRootPages()
    {
        $route = new Route(new MarkdownPage('foo'));

        $this->assertSame(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertSame('foo.html', $route->getLink());
    }

    public function testGetLinkReturnsCorrectPathForNestedPages()
    {
        $route = new Route(new MarkdownPage('foo/bar'));

        $this->assertSame(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertSame('foo/bar.html', $route->getLink());
    }

    public function testGetLinkReturnsCorrectPathForNestedCurrentPage()
    {
        $route = new Route(new MarkdownPage('foo'));
        Render::shouldReceive('getRouteKey')->andReturn('foo/bar');

        $this->assertSame(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertSame('../foo.html', $route->getLink());
    }

    public function testGetLinkReturnsPrettyUrlIfEnabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $route = new Route(new MarkdownPage('foo'));

        $this->assertSame(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertSame('foo', $route->getLink());
    }

    public function testToStringIsAliasForGetLink()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertSame($route->getLink(), (string) $route);
    }

    public function testIsWithRouteReturnsTrueWhenTrue()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is($route));
    }

    public function testIsWithRouteReturnsFalseWhenFalse()
    {
        $route = new Route(new MarkdownPage('foo'));
        $route2 = new Route(new MarkdownPage('bar'));
        $this->assertFalse($route->is($route2));
    }

    public function testIsWithRouteKeyReturnsTrueWhenTrue()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is('foo'));
    }

    public function testIsWithRouteKeyReturnsFalseWhenFalse()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertFalse($route->is('bar'));
    }

    public function testIsWithRouteKeyObjectReturnsTrueWhenTrue()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is(new RouteKey('foo')));
    }

    public function testIsWithRouteKeyObjectReturnsTrueWhenFalse()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertFalse($route->is(new RouteKey('bar')));
    }

    public function testToArrayMethod()
    {
        $this->assertSame([
            'routeKey' => 'foo',
            'sourcePath' => '_pages/foo.md',
            'outputPath' => 'foo.html',
            'page' => [
                'class' => MarkdownPage::class,
                'identifier' => 'foo',
            ],
        ], (new MarkdownPage('foo'))->getRoute()->toArray());
    }
}
