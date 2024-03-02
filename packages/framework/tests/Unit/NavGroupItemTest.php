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
        $item = new NavGroupItem('Foo', $children);

        $this->assertSame($children, $item->getChildren());
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

    public function testCanAddItemToDropdown()
    {
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child 1', group: 'foo');

        $this->assertSame([$child], $group->addChild($child)->getChildren());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame($group, $group->addChild($child));
    }

    public function testCanAddMultipleItemsToDropdown()
    {
        $group = new NavGroupItem('Foo');
        $items = $this->createNavItems();

        $this->assertSame($items, $group->addChildren($items)->getChildren());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $group = new NavGroupItem('Foo');

        $this->assertSame($group, $group->addChildren([]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Child', group: 'bar');

        $group->addChild($child);

        $this->assertSame('foo', $group->getGroupIdentifier());
        $this->assertSame('bar', $child->getGroupIdentifier());
    }

    public function testAddingAnItemWithNoGroupKeyUsesGroupIdentifier()
    {
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');

        $group->addChild($child);

        $this->assertSame('foo', $group->getGroupIdentifier());
        $this->assertSame('foo', $child->getGroupIdentifier());
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
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new MarkdownPage()), 'Bar');
        $group->addChild($child);
        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityWithDocumentationPageChildrenUsesLowestPriority()
    {
        $group = new NavGroupItem('Foo');
        $child = new NavItem(new Route(new DocumentationPage()), 'Foo', 100);
        $group->addChild($child);
        $this->assertSame(100, $group->getPriority());
    }

    public function testGetPriorityUsesGroupPriorityForMixedChildTypes()
    {
        $group = new NavGroupItem('Foo');

        foreach (HydeCoreExtension::getPageClasses() as $type) {
            $child = new NavItem(new Route(new $type()), 'Bar', 100);
            $group->addChild($child);
        }

        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityHandlesStringUrlChildGracefully()
    {
        $this->assertSame(999, (new NavGroupItem('Foo', [new NavItem('foo', 'Bar', 100)]))->getPriority());
    }

    public function testGetPriorityHandlesExternalUrlChildGracefully()
    {
        $this->assertSame(999, (new NavGroupItem('Foo', [new NavItem('https://example.com', 'Bar', 100)]))->getPriority());
    }

    private function createNavItems(): array
    {
        return [
            new NavItem(new Route(new InMemoryPage('foo')), 'Foo'),
            new NavItem(new Route(new InMemoryPage('bar')), 'Bar'),
        ];
    }
}
