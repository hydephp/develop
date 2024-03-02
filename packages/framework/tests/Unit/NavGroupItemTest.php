<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RenderData;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\NavGroupItem;

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
        $children = $this->createNavItems();
        $item = new NavGroupItem('Test', $children, 500);

        $this->assertSame('Test', $item->getLabel());
        $this->assertNull($item->getRoute());
        $this->assertSame(500, $item->getPriority());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
    }

    public function testCanConstructWithChildrenWithoutRoute()
    {
        $children = $this->createNavItems();
        $item = new NavGroupItem('Test', $children, 500);

        $this->assertSame('Test', $item->getLabel());
        $this->assertNull($item->getRoute());

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
    }

    public function testGetChildren()
    {
        $children = $this->createNavItems();

        $navItem = new NavGroupItem('Parent', $children, 500);
        $this->assertSame($children, $navItem->getChildren());
    }

    public function testGetChildrenWithNoChildren()
    {
        $navItem = new NavGroupItem('Parent');
        $this->assertEmpty($navItem->getChildren());
    }

    public function testHasChildren()
    {
        $item = new NavGroupItem('Parent');
        $this->assertFalse($item->hasChildren());
    }

    public function testHasChildrenWithChildren()
    {
        $item = new NavGroupItem('Parent', $this->createNavItems(), 500);
        $this->assertTrue($item->hasChildren());
    }

    public function testCanAddMultipleItemsToDropdown()
    {
        $parent = new NavGroupItem('Parent');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', group: 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', group: 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChildren([$child1, $child2]);

        $this->assertSame([$child1, $child2], $parent->getChildren());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $parent = new NavGroupItem('Parent');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', group: 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', group: 'foo');

        $this->assertSame($parent, $parent->addChildren([$child1, $child2]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', group: 'bar');

        $parent->addChild($child);

        $this->assertSame('parent', $parent->getGroupIdentifier());
        $this->assertSame('bar', $child->getGroupIdentifier());
    }

    public function testAddingAnItemWithNoGroupKeyUsesParentIdentifier()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child');

        $parent->addChild($child);

        $this->assertSame('parent', $parent->getGroupIdentifier());
        $this->assertSame('parent', $child->getGroupIdentifier());
    }

    public function testCanAddItemToDropdown()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', group: 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChild($child);

        $this->assertSame([$child], $parent->getChildren());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', group: 'foo');

        $this->assertSame($parent, $parent->addChild($child));
    }

    public function testGetPriorityUsesDefaultPriority()
    {
        $parent = new NavGroupItem('Parent');
        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityWithNoChildrenUsesGroupPriority()
    {
        $parent = new NavGroupItem('Parent');
        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityWithChildrenUsesGroupPriority()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', 400, 'foo');
        $parent->addChild($child);
        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityWithDocumentationPageChildrenUsesLowestPriority()
    {
        $parent = new NavGroupItem('Parent');
        $child = new NavItem(new Route(new DocumentationPage()), 'Foo', 400);
        $parent->addChild($child);
        $this->assertSame(400, $parent->getPriority());
    }

    public function testGetPriorityHandlesMixedChildTypes()
    {
        $parent = new NavGroupItem('Parent');

        foreach (HydeCoreExtension::getPageClasses() as $type) {
            $child = new NavItem(new Route(new $type()), 'Child', 100);
            $parent->addChild($child);
        }

        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityHandlesExternalUrlChild()
    {
        $parent = new NavGroupItem('Parent');

        $child = new NavItem('foo', 'Child', 400);
        $parent->addChild($child);

        $this->assertSame(999, $parent->getPriority());
    }

    private function createNavItems(): array
    {
        return [
            new NavItem(new Route(new InMemoryPage('foo')), ucfirst('foo')),
            new NavItem(new Route(new InMemoryPage('bar')), ucfirst('bar')),
        ];
    }
}
