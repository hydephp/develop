<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Framework\Features\Navigation\NavItem;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavGroupItem
 */
class NavGroupItemTest extends UnitTestCase
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

    public function testCanConstructWithChildren()
    {
        $route = new Route(new MarkdownPage());
        $children = [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo', 500),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar', 500),
        ];
        $item = new NavItem($route, 'Test', 500, null, $children);

        $this->assertSame('Test', $item->getLabel());
        $this->assertNull($item->getRoute());
        $this->assertSame(500, $item->getPriority());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());

        $this->assertSame('Foo', $item->getChildren()[0]->getLabel());
        $this->assertSame('Bar', $item->getChildren()[1]->getLabel());

        $this->assertSame('foo.html', $item->getChildren()[0]->getUrl());
        $this->assertSame('bar.html', $item->getChildren()[1]->getUrl());

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
        $this->assertNull($item->getRoute());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
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

    public function testCanAddMultipleItemsToDropdown()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', 500, 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', 500, 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChildren([$child1, $child2]);

        $this->assertSame([$child1, $child2], $parent->getChildren());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', 500, 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', 500, 'foo');

        $this->assertSame($parent, $parent->addChildren([$child1, $child2]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 500, 'bar');

        $parent->addChild($child);

        $this->assertSame('foo', $parent->getGroupIdentifier());
        $this->assertSame('bar', $child->getGroupIdentifier());
    }

    public function testAddingAnItemWithNoGroupKeyUsesParentIdentifier()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 500);

        $parent->addChild($child);

        $this->assertSame('foo', $parent->getGroupIdentifier());
        $this->assertSame('foo', $child->getGroupIdentifier());
    }

    public function testCanAddItemToDropdown()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 500, 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChild($child);

        $this->assertSame([$child], $parent->getChildren());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $parent = new NavItem(new Route(new MarkdownPage()), 'Parent', 500, 'foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 500, 'foo');

        $this->assertSame($parent, $parent->addChild($child));
    }
}
