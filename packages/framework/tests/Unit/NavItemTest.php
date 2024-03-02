<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\ExternalRoute;
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
 * For the full feature test, see the MainNavigationMenuTest class.
 *
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 *
 * @see \Hyde\Framework\Testing\Unit\NavItemIsActiveHelperTest
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

    public function testConstruct()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame($route, $item->getRoute());
    }

    public function testPassingRouteInstanceToConstructorUsesRouteInstance()
    {
        $route = new Route(new MarkdownPage());
        $this->assertSame($route, (new NavItem($route, 'Home'))->getRoute());
    }

    public function testPassingRouteKeyToConstructorUsesRouteInstance()
    {
        $route = Routes::get('index');

        $this->assertSame($route, (new NavItem('index', 'Home'))->getRoute());
    }

    public function testPassingUrlToConstructorUsesExternalRoute()
    {
        $item = new NavItem('https://example.com', 'Home');
        $this->assertInstanceOf(ExternalRoute::class, $item->getRoute());
        $this->assertEquals(new ExternalRoute('https://example.com'), $item->getRoute());
        $this->assertSame('https://example.com', (string) $item->getRoute());
    }

    public function testPassingUnknownRouteKeyToConstructorUsesExternalRoute()
    {
        $item = new NavItem('foo', 'Home');
        $this->assertInstanceOf(ExternalRoute::class, $item->getRoute());
        $this->assertEquals(new ExternalRoute('foo'), $item->getRoute());
        $this->assertSame('foo', (string) $item->getRoute());
    }

    public function testGetDestination()
    {
        $route = new Route(new InMemoryPage('foo'));
        $navItem = new NavItem($route, 'Page', 500);

        $this->assertSame($route, $navItem->getRoute());
    }

    public function testGetLink()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame('foo.html', $navItem->getUrl());
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
        $this->assertNull($navItem->getGroupIdentifier());
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());
        $item = NavItem::forRoute($route);

        $this->assertSame($route, $item->getRoute());
    }

    public function testToString()
    {
        Render::shouldReceive('getRouteKey')->once()->andReturn('index');

        $this->assertSame('index.html', (string) NavItem::forRoute(Routes::get('index')));
    }

    public function testForLink()
    {
        $item = NavItem::forLink('foo', 'bar');

        $this->assertEquals(new ExternalRoute('foo'), $item->getRoute());
        $this->assertSame('bar', $item->getLabel());
        $this->assertSame(500, $item->getPriority());
    }

    public function testForLinkWithCustomPriority()
    {
        $this->assertSame(100, NavItem::forLink('foo', 'bar', 100)->getPriority());
    }

    public function testForRoute()
    {
        $route = Routes::get('404');
        $item = NavItem::forRoute($route, 'foo');

        $this->assertSame($route, $item->getRoute());
        $this->assertSame('foo', $item->getLabel());
        $this->assertSame(999, $item->getPriority());
    }

    public function testForIndexRoute()
    {
        $route = Routes::get('index');
        $item = NavItem::forRoute($route, 'foo');

        $this->assertSame($route, $item->getRoute());
        $this->assertSame('foo', $item->getLabel());
        $this->assertSame(0, $item->getPriority());
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
        $this->assertSame(100, NavItem::forRoute(Routes::get('index'), 'foo', 100)->getPriority());
    }

    public function testRouteBasedNavItemDestinationsAreResolvedRelatively()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));

        $this->assertSame('foo.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('foo/bar.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar')),
            'getRouteKey' => 'foo/bar',
        ]));

        $this->assertSame('../foo.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../foo/bar.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar/baz')),
            'getRouteKey' => 'foo/bar/baz',
        ]));

        $this->assertSame('../../foo.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../../foo/bar.html', (string) NavItem::forRoute(new Route(new InMemoryPage('foo/bar'))));
    }

    public function testDropdownFacade()
    {
        $item = NavItem::forGroup('foo', []);

        $this->assertSame('foo', $item->getLabel());
        $this->assertSame([], $item->getItems());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithChildren()
    {
        $children = [
            new NavItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = NavItem::forGroup('foo', $children);
        $this->assertSame($children, $item->getItems());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithCustomPriority()
    {
        $item = NavItem::forGroup('foo', [], 500);

        $this->assertSame(500, $item->getPriority());
    }

    public function testIsCurrent()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertTrue(NavItem::forRoute(new Route(new InMemoryPage('foo')))->isActive());
        $this->assertFalse(NavItem::forRoute(new Route(new InMemoryPage('bar')))->isActive());
    }

    public function testIsCurrentWithExternalRoute()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertFalse(NavItem::forLink('foo', 'bar')->isActive());
        $this->assertFalse(NavItem::forLink('https://example.com', 'bar')->isActive());
    }

    public function testGetGroupWithNoGroup()
    {
        $this->assertNull((new NavItem(new Route(new MarkdownPage()), 'Test', 500))->getGroupIdentifier());
    }

    public function testGetGroupWithGroup()
    {
        $this->assertSame('foo', (new NavItem(new Route(new MarkdownPage()), 'Test', 500, 'foo'))->getGroupIdentifier());
    }

    public function testGetGroupFromRouteWithGroup()
    {
        $this->assertSame('foo', NavItem::forRoute(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])))->getGroupIdentifier());
    }

    public function testGetGroupForRouteWithGroup()
    {
        $this->assertSame('foo', NavItem::forRoute(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])), 'foo')->getGroupIdentifier());
    }

    public function testGroupKeysAreNormalized()
    {
        $item = new NavItem(new Route(new MarkdownPage()), 'Test', 500, 'Foo Bar');
        $this->assertSame('foo-bar', $item->getGroupIdentifier());
    }

    public function testIdentifier()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame('test', $item->getIdentifier());
    }

    public function testIdentifierWithCustomLabel()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Foo Bar', 500);

        $this->assertSame('foo-bar', $item->getIdentifier());
    }

    public function testIdentifierFromRouteKey()
    {
        $item = NavItem::forRoute(Routes::get('index'));
        $this->assertSame('home', $item->getIdentifier());
    }

    public function testIdentifierUsesLabelWhenRouteKeyIsFalsy()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Foo Bar', 500);

        $this->assertSame('foo-bar', $item->getIdentifier());
    }

    public function testIdentifierUsesLabelForExternalRoute()
    {
        $item = NavItem::forLink('https://example.com', 'Foo Bar');
        $this->assertSame('foo-bar', $item->getIdentifier());
    }
}
