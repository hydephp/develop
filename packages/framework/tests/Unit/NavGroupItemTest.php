<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
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
        self::needsKernel();
        self::mockConfig();
    }

    public function testCanConstruct()
    {
        $this->assertSame('Foo', (new NavGroupItem('Foo'))->getLabel());
    }

    public function testCanConstructWithPriority()
    {
        $this->assertSame(500, (new NavGroupItem('Foo', priority: 500))->getPriority());
    }

    public function testDefaultPriorityValueIsLast()
    {
        $this->assertSame(999, (new NavGroupItem('Foo'))->getPriority());
    }

    public function testDestinationIsAlwaysNull()
    {
        $this->assertNull((new NavGroupItem('Foo'))->getRoute());
    }

    public function testCanConstructWithChildren()
    {
        $children = $this->createNavItems();
        $item = new NavGroupItem('Foo', $children);

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
    }

    public function testCanConstructWithChildrenWithoutRoute()
    {
        $children = $this->createNavItems();
        $item = new NavGroupItem('Foo', $children);

        $this->assertCount(2, $item->getChildren());
        $this->assertSame($children, $item->getChildren());
    }

    public function testGetChildren()
    {
        $children = $this->createNavItems();

        $navItem = new NavGroupItem('Foo', $children);
        $this->assertSame($children, $navItem->getChildren());
    }

    public function testGetChildrenWithNoChildren()
    {
        $this->assertEmpty((new NavGroupItem('Foo'))->getChildren());
    }

    public function testHasChildren()
    {
        $this->assertFalse((new NavGroupItem('Foo'))->hasChildren());
    }

    public function testHasChildrenWithChildren()
    {
        $this->assertTrue((new NavGroupItem('Foo', $this->createNavItems()))->hasChildren());
    }

    public function testCanAddMultipleItemsToDropdown()
    {
        $parent = new NavGroupItem('Foo');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', group: 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', group: 'foo');

        $this->assertSame([], $parent->getChildren());

        $parent->addChildren([$child1, $child2]);

        $this->assertSame([$child1, $child2], $parent->getChildren());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $parent = new NavGroupItem('Foo');
        $child1 = new NavItem(new Route(new MarkdownPage()), 'Child 1', group: 'foo');
        $child2 = new NavItem(new Route(new MarkdownPage()), 'Child 2', group: 'foo');

        $this->assertSame($parent, $parent->addChildren([$child1, $child2]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', group: 'bar');

        $parent->addChild($child);

        $this->assertSame('foo', $parent->getGroupIdentifier());
        $this->assertSame('bar', $child->getGroupIdentifier());
    }

    public function testAddingAnItemWithNoGroupKeyUsesGroupIdentifier()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');

        $parent->addChild($child);

        $this->assertSame('foo', $parent->getGroupIdentifier());
        $this->assertSame('foo', $child->getGroupIdentifier());
    }

    public function testCanAddItemToDropdown()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame([], $parent->getChildren());

        $parent->addChild($child);

        $this->assertSame([$child], $parent->getChildren());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame($parent, $parent->addChild($child));
    }

    public function testGetPriorityUsesDefaultPriority()
    {
        $this->assertSame(999, (new NavGroupItem('Foo'))->getPriority());
    }

    public function testGetPriorityWithNoChildrenUsesGroupPriority()
    {
        $this->assertSame(999, (new NavGroupItem('Foo'))->getPriority());
    }

    public function testGetPriorityWithChildrenUsesGroupPriority()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');
        $parent->addChild($child);
        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityWithDocumentationPageChildrenUsesLowestPriority()
    {
        $parent = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new DocumentationPage()), 'Foo', 100);
        $parent->addChild($child);
        $this->assertSame(100, $parent->getPriority());
    }

    public function testGetPriorityUsesGroupPriorityForMixedChildTypes()
    {
        $parent = new NavGroupItem('Foo');

        foreach (HydeCoreExtension::getPageClasses() as $type) {
            $child = new NavItem(new Route(new $type()), 'Child', 100);
            $parent->addChild($child);
        }

        $this->assertSame(999, $parent->getPriority());
    }

    public function testGetPriorityHandlesExternalUrlChildGracefully()
    {
        $parent = new NavGroupItem('Foo');

        $child = new NavItem('foo', 'Child', 100);
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
