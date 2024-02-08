<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Mockery;

/**
 * This unit test covers the basics of the NavItem class.
 * For the full feature test, see the NavigationMenuTest class.
 *
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 *
 * @see \Hyde\Framework\Testing\Unit\NavItemIsCurrentHelperTest
 */
class NavItemTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$hasSetUpKernel = false;

        self::needsKernel();
        self::mockConfig();
    }

    protected function setUp(): void
    {
        Render::swap(new RenderData());
    }

    public function test__construct()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame($route, $item->destination);
    }

    public function testGetDestination()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame($route, $item->getDestination());
    }

    public function testGetLink()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame('foo.html', $navItem->getLink());
    }

    public function testGetLabel()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame('Page', $navItem->getLabel());
    }

    public function testGetPriority()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame(500, $navItem->getPriority());
    }

    public function testGetGroup()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertNull($navItem->getGroup());
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());
        $item = NavItem::fromRoute($route);

        $this->assertSame($route, $item->destination);
    }

    public function test__toString()
    {
        Render::shouldReceive('getRouteKey')->once()->andReturn('index');

        $this->assertSame('index.html', (string) NavItem::fromRoute(Routes::get('index')));
    }

    public function testForLink()
    {
        $item = NavItem::forLink('foo', 'bar');

        $this->assertSame('foo', $item->destination);
        $this->assertSame('bar', $item->label);
        $this->assertSame(500, $item->priority);
    }

    public function testForLinkWithCustomPriority()
    {
        $this->assertSame(100, NavItem::forLink('foo', 'bar', 100)->priority);
    }

    public function testForRoute()
    {
        $route = Routes::get('404');
        $item = NavItem::forRoute($route, 'foo');

        $this->assertSame($route, $item->destination);
        $this->assertSame('foo', $item->label);
        $this->assertSame(999, $item->priority);
    }

    public function testForIndexRoute()
    {
        $route = Routes::get('index');
        $item = NavItem::forRoute($route, 'foo');

        $this->assertSame($route, $item->destination);
        $this->assertSame('foo', $item->label);
        $this->assertSame(0, $item->priority);
    }

    public function testForRouteWithRouteKey()
    {
        $this->assertEquals(
            NavItem::forRoute(Routes::get('index'), 'foo'),
            NavItem::forRoute('index', 'foo')
        );
    }

    public function testForRouteWithMissingRouteKey()
    {
        $this->expectException(RouteNotFoundException::class);
        NavItem::forRoute('foo', 'foo');
    }

    public function testForRouteWithCustomPriority()
    {
        $this->assertSame(100, NavItem::forRoute(Routes::get('index'), 'foo', 100)->priority);
    }

    public function testRouteBasedNavItemDestinationsAreResolvedRelatively()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => (new Route(new InMemoryPage('foo'))),
            'getRouteKey' => 'foo',
        ]));

        $this->assertSame('foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => (new Route(new InMemoryPage('foo/bar'))),
            'getRouteKey' => 'foo/bar',
        ]));

        $this->assertSame('../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => (new Route(new InMemoryPage('foo/bar/baz'))),
            'getRouteKey' => 'foo/bar/baz',
        ]));

        $this->assertSame('../../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));
    }

    public function testIsCurrent()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => (new Route(new InMemoryPage('foo'))),
            'getRouteKey' => 'foo',
        ]));
        $this->assertTrue(NavItem::fromRoute(new Route(new InMemoryPage('foo')))->isCurrent());
        $this->assertFalse(NavItem::fromRoute(new Route(new InMemoryPage('bar')))->isCurrent());
    }

    public function testGetGroupWithNoGroup()
    {
        $this->assertNull((new NavItem(new Route(new MarkdownPage()), 'Test', 500))->getGroup());
    }

    public function testGetGroupWithGroup()
    {
        $this->assertSame('foo', (new NavItem(new Route(new MarkdownPage()), 'Test', 500, 'foo'))->getGroup());
    }

    public function testGetGroupFromRouteWithGroup()
    {
        $this->assertSame('foo', NavItem::fromRoute(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])))->getGroup());
    }

    public function testGetGroupForRouteWithGroup()
    {
        $this->assertSame('foo', NavItem::forRoute(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])), 'foo')->getGroup());
    }
}
