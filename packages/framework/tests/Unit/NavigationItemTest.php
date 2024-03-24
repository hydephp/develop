<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\ExternalRoute;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Mockery;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\NavigationElement;

/**
 * This unit test covers the basics of the NavigationItem class.
 * For the full feature test, see the MainNavigationMenuTest class.
 *
 * @covers \Hyde\Framework\Features\Navigation\NavigationItem
 *
 * @see \Hyde\Framework\Testing\Unit\NavigationItemIsActiveHelperTest
 */
class NavigationItemTest extends UnitTestCase
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
        $item = new NavigationItem($route, 'Test', 500);

        $this->assertSame($route, $item->getRoute());
    }

    public function testIsInstanceOfNavigationElement()
    {
        $this->assertInstanceOf(NavigationElement::class, new NavigationItem(new Route(new MarkdownPage()), 'Test', 500));
    }

    public function testPassingRouteInstanceToConstructorUsesRouteInstance()
    {
        $route = new Route(new MarkdownPage());
        $this->assertSame($route, (new NavigationItem($route, 'Home'))->getRoute());
    }

    public function testPassingRouteKeyToConstructorUsesRouteInstance()
    {
        $route = Routes::get('index');

        $this->assertSame($route, (new NavigationItem('index', 'Home'))->getRoute());
    }

    public function testPassingUrlToConstructorSetsRouteToNull()
    {
        $item = new NavigationItem('https://example.com', 'Home');
        $this->assertNull($item->getRoute());
        $this->assertSame('https://example.com', $item->getUrl());
    }

    public function testPassingUnknownRouteKeyToConstructorSetsRouteToNull()
    {
        $item = new NavigationItem('foo', 'Home');
        $this->assertNull($item->getRoute());
        $this->assertSame('foo', $item->getUrl());
    }

    public function testGetDestination()
    {
        $route = new Route(new InMemoryPage('foo'));
        $NavigationItem = new NavigationItem($route, 'Page', 500);

        $this->assertSame($route, $NavigationItem->getRoute());
    }

    public function testGetLink()
    {
        $NavigationItem = new NavigationItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame('foo.html', $NavigationItem->getUrl());
    }

    public function testGetLabel()
    {
        $NavigationItem = new NavigationItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame('Page', $NavigationItem->getLabel());
    }

    public function testGetPriority()
    {
        $NavigationItem = new NavigationItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertSame(500, $NavigationItem->getPriority());
    }

    public function testGetGroup()
    {
        $NavigationItem = new NavigationItem(new Route(new InMemoryPage('foo')), 'Page', 500);
        $this->assertNull($NavigationItem->getGroupKey());
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());
        $item = NavigationItem::create($route);

        $this->assertSame($route, $item->getRoute());
    }

    public function testToString()
    {
        Render::shouldReceive('getRouteKey')->once()->andReturn('index');

        $this->assertSame('index.html', (string) NavigationItem::create(Routes::get('index')));
    }

    public function testCreateWithLink()
    {
        $item = NavigationItem::create('foo', 'bar');

        $this->assertEquals(new ExternalRoute('foo'), $item->getRoute());
        $this->assertSame('bar', $item->getLabel());
        $this->assertSame(500, $item->getPriority());
    }

    public function testCreateWithLinkWithCustomPriority()
    {
        $this->assertSame(100, NavigationItem::create('foo', 'bar', 100)->getPriority());
    }

    public function testCreate()
    {
        $route = Routes::get('404');
        $item = NavigationItem::create($route, 'foo');

        $this->assertSame($route, $item->getRoute());
        $this->assertSame('foo', $item->getLabel());
        $this->assertSame(999, $item->getPriority());
    }

    public function testForIndexRoute()
    {
        $route = Routes::get('index');
        $item = NavigationItem::create($route, 'foo');

        $this->assertSame($route, $item->getRoute());
        $this->assertSame('foo', $item->getLabel());
        $this->assertSame(0, $item->getPriority());
    }

    public function testCreateWithRouteKey()
    {
        $this->assertEquals(
            NavigationItem::create(Routes::get('index'), 'foo'),
            NavigationItem::create('index', 'foo')
        );
    }

    public function testCreateWithMissingRouteKey()
    {
        $this->assertInstanceOf(ExternalRoute::class, NavigationItem::create('foo', 'foo')->getRoute());
    }

    public function testCreateWithCustomPriority()
    {
        $this->assertSame(100, NavigationItem::create(Routes::get('index'), 'foo', 100)->getPriority());
    }

    public function testRouteBasedNavigationItemDestinationsAreResolvedRelatively()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));

        $this->assertSame('foo.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo'))));
        $this->assertSame('foo/bar.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar')),
            'getRouteKey' => 'foo/bar',
        ]));

        $this->assertSame('../foo.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../foo/bar.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo/bar/baz')),
            'getRouteKey' => 'foo/bar/baz',
        ]));

        $this->assertSame('../../foo.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../../foo/bar.html', (string) NavigationItem::create(new Route(new InMemoryPage('foo/bar'))));
    }

    public function testDropdownFacade()
    {
        $item = NavigationGroup::create('foo');

        $this->assertSame('foo', $item->getLabel());
        $this->assertSame([], $item->getItems());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithChildren()
    {
        $children = [
            new NavigationItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = NavigationGroup::create('foo', $children);
        $this->assertSame($children, $item->getItems());
        $this->assertSame(999, $item->getPriority());
    }

    public function testDropdownFacadeWithCustomPriority()
    {
        $item = NavigationGroup::create('foo', [], 500);

        $this->assertSame(500, $item->getPriority());
    }

    public function testIsCurrent()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertTrue(NavigationItem::create(new Route(new InMemoryPage('foo')))->isActive());
        $this->assertFalse(NavigationItem::create(new Route(new InMemoryPage('bar')))->isActive());
    }

    public function testIsCurrentWithExternalRoute()
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => new Route(new InMemoryPage('foo')),
            'getRouteKey' => 'foo',
        ]));
        $this->assertFalse(NavigationItem::create('foo', 'bar')->isActive());
        $this->assertFalse(NavigationItem::create('https://example.com', 'bar')->isActive());
    }

    public function testGetGroupWithNoGroup()
    {
        $this->assertNull((new NavigationItem(new Route(new MarkdownPage()), 'Test', 500))->getGroupKey());
    }

    public function testGetGroupWithGroup()
    {
        $this->assertSame('foo', (new NavigationItem(new Route(new MarkdownPage()), 'Test', 500, 'foo'))->getGroupKey());
    }

    public function testGetGroupFromRouteWithGroup()
    {
        $this->assertSame('foo', NavigationItem::create(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])))->getGroupKey());
    }

    public function testGetGroupCreateWithGroup()
    {
        $this->assertSame('foo', NavigationItem::create(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])), 'foo')->getGroupKey());
    }

    public function testGroupKeysAreNormalized()
    {
        $item = new NavigationItem(new Route(new MarkdownPage()), 'Test', 500, 'Foo Bar');
        $this->assertSame('foo-bar', $item->getGroupKey());
    }

    public function testNormalizeGroupKeyCreatesSlugs()
    {
        $this->assertSame('foo-bar', NavigationItem::normalizeGroupKey('Foo Bar'));
        $this->assertSame('foo-bar', NavigationItem::normalizeGroupKey('foo bar'));
        $this->assertSame('foo-bar', NavigationItem::normalizeGroupKey('foo_bar'));
        $this->assertSame('foo-bar', NavigationItem::normalizeGroupKey('foo-bar'));
        $this->assertSame('foo-bar', NavigationItem::normalizeGroupKey(' foo bar '));
    }

    public function testNormalizeGroupKeyReturnsNullForNull()
    {
        $this->assertNull(NavigationItem::normalizeGroupKey(null));
    }
}
