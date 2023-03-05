<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Mockery;

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

        $this->assertSame('foo', $item->href);
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

    public function testToRouteWithCustomPriority()
    {
        $this->assertSame(100, NavItem::toRoute(\Hyde\Facades\Route::get('index'), 'foo', 100)->priority);
    }

    public function testIsCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertTrue(NavItem::fromRoute($this->makeRoute('foo'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentRoute()
    {
        $this->mockRenderData($this->makeRoute('index'));
        $this->assertTrue(NavItem::fromRoute(\Hyde\Facades\Route::get('index'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentLink()
    {
        $this->mockRenderData($this->makeRoute('index'));
        $this->assertTrue(NavItem::toLink('index.html', 'Home')->isCurrent());
    }

    public function testIsCurrentWhenNotCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentRoute()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::fromRoute(\Hyde\Facades\Route::get('index'))->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentLink()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::toLink('index.html', 'Home')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::fromRoute($this->makeRoute('foo/bar'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::fromRoute($this->makeRoute('foo/bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('foo/baz'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertTrue(NavItem::fromRoute($this->makeRoute('foo/bar/baz'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('foo/baz/bar'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNested()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('foo/bar/baz'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverse()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::fromRoute($this->makeRoute('foo'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentLinkWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::toLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentLinkWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::toLink('foo.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageAndSubjectPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::toLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenNotCurrentWithNestedCurrentPageAndSubjectPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::toLink('foo/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::toLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::toLink('foo/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertTrue(NavItem::toLink('foo/bar/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::toLink('foo/baz/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::toLink('foo/bar/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverseUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::toLink('foo.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::toLink('foo/bar', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::toLink('foo/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertTrue(NavItem::toLink('foo/bar/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::toLink('foo/baz/bar', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::toLink('foo/bar/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverseUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::toLink('foo', 'foo')->isCurrent());
    }

    public function testGetGroup()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500);

        $this->assertNull($item->getGroup());
    }

    public function testGetGroupWithGroup()
    {
        $route = new Route(new MarkdownPage());
        $item = new NavItem($route, 'Test', 500, 'foo');

        $this->assertSame('foo', $item->getGroup());
    }

    public function testGetGroupFromRouteWithGroup()
    {
        $route = new Route(new MarkdownPage(matter: ['navigation.group' => 'foo']));
        $item = NavItem::fromRoute($route);

        $this->assertSame('foo', $item->getGroup());
    }

    public function testGetGroupToRouteWithGroup()
    {
        $route = new Route(new MarkdownPage(matter: ['navigation.group' => 'foo']));
        $item = NavItem::toRoute($route, 'foo');

        $this->assertSame('foo', $item->getGroup());
    }

    protected function mockRenderData(Route $route): void
    {
        Render::swap(Mockery::mock(\Hyde\Support\Models\Render::class, [
            'getCurrentRoute' => $route,
            'getCurrentPage' => $route->getRouteKey(),
        ]));
    }

    protected function makeRoute(string $identifier): Route
    {
        return new Route(new InMemoryPage($identifier));
    }
}
