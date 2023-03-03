<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
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
        Render::swap(new \Hyde\Support\Models\Render());
    }

    public function testConstructorCreatesRouteFromPageModel()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertInstanceOf(Route::class, $route);
    }

    public function testGetPageTypeReturnsFullyQualifiedClassName()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals(MarkdownPage::class, $route->getPageClass());
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
        $route = new Route($page);

        $this->assertEquals($page->getRouteKey(), $route->getRouteKey());
    }

    public function testGetSourceFilePathReturnsPageSourcePath()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getSourcePath(), $route->getSourcePath());
    }

    public function testGetOutputFilePathReturnsPageOutputPath()
    {
        $page = new MarkdownPage();
        $route = new Route($page);

        $this->assertEquals($page->getOutputPath(), $route->getOutputPath());
    }

    public function testGetLinkReturnsCorrectPathForRootPages()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo.html', $route->getLink());
    }

    public function testGetLinkReturnsCorrectPathForNestedPages()
    {
        $route = new Route(new MarkdownPage('foo/bar'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo/bar.html', $route->getLink());
    }

    public function testGetLinkReturnsCorrectPathForNestedCurrentPage()
    {
        $route = new Route(new MarkdownPage('foo'));
        Render::shouldReceive('getCurrentPage')->andReturn('foo/bar');
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('../foo.html', $route->getLink());
    }

    public function testGetLinkReturnsPrettyUrlIfEnabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals(Hyde::relativeLink($route->getOutputPath()), $route->getLink());
        $this->assertEquals('foo', $route->getLink());
    }

    public function testToStringIsAliasForGetLink()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertEquals($route->getLink(), (string) $route);
    }

    public function testIsWithRoute()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is($route));

        $route2 = new Route(new MarkdownPage('bar'));
        $this->assertFalse($route->is($route2));
    }

    public function testIsWithRouteKey()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is('foo'));
        $this->assertFalse($route->is('bar'));
    }

    public function testIsWithRouteKeyObject()
    {
        $route = new Route(new MarkdownPage('foo'));
        $this->assertTrue($route->is(new RouteKey('foo')));
        $this->assertFalse($route->is(new RouteKey('bar')));
    }

    public function testToArrayMethod()
    {
        $this->assertEquals([
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
