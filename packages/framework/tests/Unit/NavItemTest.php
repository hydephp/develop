<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
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
        self::needsKernel();
        self::mockConfig();
    }

    protected function setUp(): void
    {
        Render::swap(new \Hyde\Support\Models\Render());
    }

    public function test__construct()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertSame($route->getLink(), $item->destination);
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());
        $item = NavItem::fromRoute($route);

        $this->assertSame($route->getLink(), $item->destination);
    }

    public function test__toString()
    {
        Render::shouldReceive('getCurrentPage')->once()->andReturn('index');

        $this->assertSame('index.html', (string) NavItem::fromRoute(\Hyde\Facades\Route::get('index')));
    }

    public function testToLink()
    {
        $item = NavItem::toLink('foo', 'bar');

        $this->assertSame('foo', $item->destination);
        $this->assertSame('bar', $item->label);
        $this->assertSame(500, $item->priority);
    }

    public function testToLinkWithCustomPriority()
    {
        $this->assertSame(100, NavItem::toLink('foo', 'bar', 100)->priority);
    }

    public function testToRoute()
    {
        $route = \Hyde\Facades\Route::get('index');
        $item = NavItem::toRoute($route, 'foo');

        $this->assertSame($route->getLink(), $item->destination);
        $this->assertSame('foo', $item->label);
        $this->assertSame(500, $item->priority);
    }

    public function testToRouteWithRouteKey()
    {
        $this->assertEquals(
            NavItem::toRoute(\Hyde\Facades\Route::get('index'), 'foo'),
            NavItem::toRoute('index', 'foo')
        );
    }

    public function testToRouteWithRouteKeyClass()
    {
        $this->assertEquals(
            NavItem::toRoute('index', 'foo'),
            NavItem::toRoute((string) new RouteKey('index'), 'foo') // String cast to emulate non-strict types
        );
    }

    public function testToRouteWithMissingRouteKey()
    {
        $this->expectException(RouteNotFoundException::class);
        NavItem::toRoute('missing', 'foo');
    }

    public function testToRouteWithCustomPriority()
    {
        $this->assertSame(100, NavItem::toRoute(\Hyde\Facades\Route::get('index'), 'foo', 100)->priority);
    }

    public function testRouteBasedNavItemDestinationsAreResolvedRelatively()
    {
        Render::swap(Mockery::mock(\Hyde\Support\Models\Render::class, [
            'getCurrentRoute' => (new Route(new InMemoryPage('foo'))),
            'getCurrentPage' => 'foo',
        ]));

        $this->assertSame('foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(\Hyde\Support\Models\Render::class, [
            'getCurrentRoute' => (new Route(new InMemoryPage('foo/bar'))),
            'getCurrentPage' => 'foo/bar',
        ]));

        $this->assertSame('../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));

        Render::swap(Mockery::mock(\Hyde\Support\Models\Render::class, [
            'getCurrentRoute' => (new Route(new InMemoryPage('foo/bar/baz'))),
            'getCurrentPage' => 'foo/bar/baz',
        ]));

        $this->assertSame('../../foo.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo'))));
        $this->assertSame('../../foo/bar.html', (string) NavItem::fromRoute(new Route(new InMemoryPage('foo/bar'))));
    }

    public function testIsCurrent()
    {
        Render::swap(Mockery::mock(\Hyde\Support\Models\Render::class, [
            'getCurrentRoute' => (new Route(new InMemoryPage('foo'))),
            'getCurrentPage' => 'foo',
        ]));
        $this->assertTrue(NavItem::fromRoute(new Route(new InMemoryPage('foo')))->isCurrent());
        $this->assertFalse(NavItem::fromRoute(new Route(new InMemoryPage('bar')))->isCurrent());
    }

    public function testGetGroup()
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

    public function testGetGroupToRouteWithGroup()
    {
        $this->assertSame('foo', NavItem::toRoute(new Route(new MarkdownPage(matter: ['navigation.group' => 'foo'])), 'foo')->getGroup());
    }
}
