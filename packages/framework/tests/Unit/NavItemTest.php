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

    public function testConstruct()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame($route, $item->getDestination());
    }

    public function testPassingRouteInstanceToConstructorUsesRouteInstance()
    {
        $route = new Route(new MarkdownPage());
        $this->assertSame($route, (new NavItem($route, 'Home'))->getDestination());
    }

    public function testPassingRouteKeyToConstructorUsesRouteInstance()
    {
        $route = Routes::get('index');

        $this->assertSame($route, (new NavItem('index', 'Home'))->getDestination());
    }

    public function testPassingUrlToConstructorUsesExternalRoute()
    {
        $item = new NavItem('https://example.com', 'Home');
        $this->assertInstanceOf(ExternalRoute::class, $item->getDestination());
        $this->assertEquals(new ExternalRoute('https://example.com'), $item->getDestination());
        $this->assertSame('https://example.com', (string) $item->getDestination());
    }

    public function testPassingUnknownRouteKeyToConstructorUsesExternalRoute()
    {
        $item = new NavItem('foo', 'Home');
        $this->assertInstanceOf(ExternalRoute::class, $item->getDestination());
        $this->assertEquals(new ExternalRoute('foo'), $item->getDestination());
        $this->assertSame('foo', (string) $item->getDestination());
    }

    public function testCanConstructWithChildren()
    {
        $route = new Route(new MarkdownPage());
        $children = [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo', 500),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar', 500),
        ];
        $item = new NavItem($route, 'Test', 500, null, $children);

        $this->assertSame('Test', $item->getLabel());
        $this->assertSame($route, $item->getDestination());
        $this->assertSame(500, $item->getPriority());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());

        $this->assertSame('Foo', $item->getChildren()[0]->getLabel());
        $this->assertSame('Bar', $item->getChildren()[1]->getLabel());

        $this->assertSame('foo.html', $item->getChildren()[0]->getLink());
        $this->assertSame('bar.html', $item->getChildren()[1]->getLink());

        $this->assertSame(500, $item->getChildren()[0]->getPriority());
        $this->assertSame(500, $item->getChildren()[1]->getPriority());
    }

    public function testCanConstructWithChildrenWithoutRoute()
    {
        $children = [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo', 500),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar', 500),
        ];
        $item = new NavItem('', 'Test', 500, null, $children);

        $this->assertSame('Test', $item->getLabel());
        $this->assertSame('', $item->getDestination()->getLink());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
    }

    public function testGetDestination()
    {
        $route = new Route(new InMemoryPage('foo'));
        $navItem = new NavItem($route, 'Page', 500);

        $this->assertSame($route, $navItem->getDestination());
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

    public function testGetChildren()
    {
        $children = [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo', 500),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar', 500),
        ];

        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500, null, $children);
        $this->assertSame($children, $navItem->getChildren());
    }

    public function testGetChildrenWithNoChildren()
    {
        $navItem = new NavItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertEmpty($navItem->getChildren());
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());
        $item = NavItem::fromRoute($route);

        $this->assertSame($route, $item->getDestination());
    }

    public function testToString()
    {
        Render::shouldReceive('getRouteKey')->once()->andReturn('index');

        $this->assertSame('index.html', (string) NavItem::fromRoute(Routes::get('index')));
    }

    public function testForLink()
    {
        $item = NavItem::forLink('foo', 'bar');

        $this->assertEquals(new ExternalRoute('foo'), $item->getDestination());
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

        $this->assertSame($route, $item->getDestination());
        $this->assertSame('foo', $item->getLabel());
        $this->assertSame(999, $item->getPriority());
    }

    public function testForIndexRoute()
    {
        $route = Routes::get('index');
        $item = NavItem::forRoute($route, 'foo');

        $this->assertSame($route, $item->getDestination());
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

        $this->assertSame('foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar')),
            'getRouteKey' => 'foo/bar',
        ]));

        $this->assertSame('../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar/baz')),
            'getRouteKey' => 'foo/bar/baz',
        ]));

        $this->assertSame('../../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));
    }

    public function testDropdownFacade()
    {
        $item = NavItem::dropdown('foo', []);

        $this->assertSame('foo', $item->getLabel());
        $this->assertSame([], $item->getChildren());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithChildren()
    {
        $children = [
            new NavItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = NavItem::dropdown('foo', $children);
        $this->assertSame($children, $item->getChildren());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithCustomPriority()
    {
        $item = NavItem::dropdown('foo', [], 500);

        $this->assertSame(500, $item->getPriority());
    }

    public function testHasChildren()
    {
        $item = new NavItem(new Route(new MarkdownPage()), 'Test', 500);
        $this->assertFalse($item->hasChildren());
    }

    public function testHasChildrenWithChildren()
    {
        $item = new NavItem(new Route(new MarkdownPage()), 'Test', 500, null, [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo', 500),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar', 500),
        ]);
        $this->assertTrue($item->hasChildren());
    }

    public function testIsCurrent()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertTrue(NavItem::fromRoute(new Route(new InMemoryPage('foo')))->isCurrent());
        $this->assertFalse(NavItem::fromRoute(new Route(new InMemoryPage('bar')))->isCurrent());
    }

    public function testIsCurrentWithExternalRoute()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertFalse(NavItem::forLink('foo', 'bar')->isCurrent());
        $this->assertFalse(NavItem::forLink('https://example.com', 'bar')->isCurrent());
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

    public function testGroupKeysAreNormalized()
    {
        $item = new NavItem(new Route(new MarkdownPage()), 'Test', 500, 'Foo Bar');
        $this->assertSame('foo-bar', $item->getGroup());
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
        $item = NavItem::fromRoute(Routes::get('index'));
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

    public function testCanAddItemToDropdown()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 500, 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChild($child);

        $this->assertSame([$child], $parent->getChildren());
    }

    public function testDefaultDropdownItemPriority()
    {
        $this->assertSame(999, NavItem::dropdown('foo', [])->getPriority());
    }
}
