<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;

/**
 * This unit test covers the basics of the NavItem class.
 * For the full feature test, see the NavigationMenuTest class.
 *
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 */
class NavItemTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function test__construct()
    {
        $route = $this->createMock(Route::class);

        $item = new NavItem($route, 'Test', 500, true);

        $this->assertSame($route, $item->route);
    }

    public function testFromRoute()
    {
        $route = $this->createMock(Route::class);

        $item = NavItem::fromRoute($route);

        $this->assertSame($route, $item->route);
    }

    public function testResolveLink()
    {
        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertSame('index.html', $item->resolveLink());
    }

    public function test__toString()
    {
        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertSame('index.html', (string) $item);
    }

    public function testToLink()
    {
        $item = NavItem::toLink('foo', 'bar', 10);

        $this->assertSame('foo', $item->href);
        $this->assertSame('bar', $item->label);
        $this->assertSame(10, $item->priority);
        $this->assertFalse($item->hidden);
    }

    public function testToRoute()
    {
        $route = Route::get('index');
        $item = NavItem::toRoute($route, 'foo', 10);

        $this->assertSame($route, $item->route);
        $this->assertSame('foo', $item->label);
        $this->assertSame(10, $item->priority);
        $this->assertFalse($item->hidden);
    }

    public function testIsCurrentRoute()
    {
        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertTrue($item->isCurrent($route->getPage()));
    }

    public function testIsCurrentLink()
    {
        $item = NavItem::toLink('index.html', 'Home');

        $this->assertTrue($item->isCurrent(Route::get('index')->getPage()));
    }

    public function testGetRoute()
    {
        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertSame($route, $item->getRoute());
    }

    public function testGetRouteWithNoRoute()
    {
        $item = NavItem::toLink('index.html', 'Home');

        $this->assertNull($item->getRoute());
    }
}
