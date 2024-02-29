<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Mockery;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 *
 * @see \Hyde\Framework\Testing\Unit\NavItemTest
 */
class NavItemIsCurrentHelperTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        Render::swap(new RenderData());
    }

    public function testIsCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertTrue(NavItem::forRoute($this->makeRoute('foo'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentRoute()
    {
        $this->mockRenderData($this->makeRoute('index'));
        $this->assertTrue(NavItem::forRoute(Routes::get('index'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentLink()
    {
        $this->mockRenderData($this->makeRoute('index'));
        $this->assertTrue(NavItem::forLink('index.html', 'Home')->isCurrent());
    }

    public function testIsCurrentWhenNotCurrent()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentRoute()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forRoute(Routes::get('index'))->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentLink()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forLink('index.html', 'Home')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::forRoute($this->makeRoute('foo/bar'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertTrue(NavItem::forRoute($this->makeRoute('foo/bar'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('foo/baz'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertTrue(NavItem::forRoute($this->makeRoute('foo/bar/baz'))->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNested()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('foo/baz/bar'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNested()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('foo/bar/baz'))->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverse()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forRoute($this->makeRoute('foo'))->isCurrent());
    }

    public function testIsCurrentUsingCurrentLinkWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentUsingNotCurrentLinkWithNestedCurrentPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageAndSubjectPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenNotCurrentWithNestedCurrentPageAndSubjectPage()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo/bar/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo/baz/bar.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forLink('foo/bar/baz.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverseUsingLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo.html', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/bar', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('foo/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo/bar/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenVeryNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo/baz/bar', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forLink('foo/bar/baz', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenVeryDifferingNestedInverseUsingPrettyLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('foo', 'foo')->isCurrent());
    }

    public function testIsCurrentWithAbsoluteLink()
    {
        $this->mockRenderData($this->makeRoute('foo'));
        $this->assertFalse(NavItem::forLink('/foo', 'foo')->isCurrent());
    }

    public function testIsCurrentWithNestedCurrentPageWhenNestedUsingAbsoluteLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar'));
        $this->assertFalse(NavItem::forLink('/foo/bar', 'foo')->isCurrent());
    }

    public function testIsCurrentWhenCurrentWithNestedCurrentPageWhenNestedUsingAbsoluteLinkItem()
    {
        $this->mockRenderData($this->makeRoute('foo/bar/baz'));
        $this->assertFalse(NavItem::forLink('/foo/bar/baz', 'foo')->isCurrent());
    }

    protected function mockRenderData(Route $route): void
    {
        Render::swap(Mockery::mock(RenderData::class, [
            'getRoute' => $route,
            'getRouteKey' => $route->getRouteKey(),
        ]));
    }

    protected function makeRoute(string $identifier): Route
    {
        return new Route(new InMemoryPage($identifier));
    }
}
