<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
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
        $route = new Route(new MarkdownPage());

        $item = new NavItem($route, 'Test', 500, true);

        $this->assertSame($route, $item->route);
    }

    public function testFromRoute()
    {
        $route = new Route(new MarkdownPage());

        $item = NavItem::fromRoute($route);

        $this->assertSame($route, $item->route);
    }

    public function testResolveLink()
    {
        Render::shouldReceive('getCurrentPage')->once()->andReturn('index');

        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertSame('index.html', $item->resolveLink());
    }

    public function test__toString()
    {
        Render::shouldReceive('getCurrentPage')->once()->andReturn('index');

        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertSame('index.html', (string) $item);
    }

    public function testToLink()
    {
        $item = NavItem::toLink('foo', 'bar');

        $this->assertSame('foo', $item->href);
        $this->assertSame('bar', $item->label);
        $this->assertSame(500, $item->priority);
        $this->assertFalse($item->hidden);
    }

    public function testToLinkWithCustomPriority()
    {
        $item = NavItem::toLink('foo', 'bar', 100);

        $this->assertSame(100, $item->priority);
    }

    public function testToRoute()
    {
        $route = Route::get('index');
        $item = NavItem::toRoute($route, 'foo');

        $this->assertSame($route, $item->route);
        $this->assertSame('foo', $item->label);
        $this->assertSame(500, $item->priority);
        $this->assertFalse($item->hidden);
    }

    public function testToRouteWithCustomPriority()
    {
        $route = Route::get('index');
        $item = NavItem::toRoute($route, 'foo', 100);

        $this->assertSame(100, $item->priority);
    }

    public function testIsCurrent()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn($this->createMock(Route::class));

        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertFalse($item->isCurrent());
    }

    public function testIsCurrentWhenCurrent()
    {
        $route = Route::get('index');

        Render::shouldReceive('getCurrentRoute')->once()->andReturn($route);
        $item = NavItem::fromRoute($route);

        $this->assertTrue($item->isCurrent());
    }

    public function testIsCurrentUsingRoute()
    {
        $route = Route::get('index');
        $item = NavItem::fromRoute($route);

        $this->assertTrue($item->isCurrent($route->getPage()));
    }

    public function testIsCurrentUsingLink()
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

    public function testSetPrioritySetsPriority()
    {
        $item = NavItem::toLink('index.html', 'Home');

        $this->assertSame(500, $item->priority);

        $item->setPriority(10);

        $this->assertSame(10, $item->priority);
    }

    public function testSetPriorityReturnsStatic()
    {
        $item = NavItem::toLink('index.html', 'Home');

        $this->assertSame($item, $item->setPriority(10));
    }

    public function testGetGroup()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500, true);

        $this->assertNull($item->getGroup());
    }

    public function testGetGroupWithGroup()
    {
        $route = new Route(new MarkdownPage(matter: ['navigation.group' => 'foo']));
        $item = new NavItem($route, 'Test', 500, true);

        $this->assertSame('foo', $item->getGroup());
    }
}
