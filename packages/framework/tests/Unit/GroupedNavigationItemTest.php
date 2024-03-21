<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\GroupedNavigationItem;

/**
 * @covers \Hyde\Framework\Features\Navigation\GroupedNavigationItem
 */
class GroupedNavigationItemTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testCanConstruct()
    {
        $this->assertSame('Foo', (new GroupedNavigationItem('Foo'))->getLabel());
    }

    public function testCanConstructWithPriority()
    {
        $this->assertSame(500, (new GroupedNavigationItem('Foo', priority: 500))->getPriority());
    }

    public function testDefaultPriorityValueIsLast()
    {
        $this->assertSame(999, (new GroupedNavigationItem('Foo'))->getPriority());
    }

    public function testDestinationIsAlwaysNull()
    {
        $this->assertNull((new GroupedNavigationItem('Foo'))->getRoute());
    }

    public function testCanConstructWithChildren()
    {
        $children = $this->createNavigationItems();
        $item = new GroupedNavigationItem('Foo', $children);

        $this->assertCount(2, $item->getItems());
        $this->assertSame($children, $item->getItems());
    }

    public function testCanConstructWithChildrenWithoutRoute()
    {
        $children = $this->createNavigationItems();
        $item = new GroupedNavigationItem('Foo', $children);

        $this->assertCount(2, $item->getItems());
        $this->assertSame($children, $item->getItems());
    }

    public function testGetItems()
    {
        $children = $this->createNavigationItems();
        $item = new GroupedNavigationItem('Foo', $children);

        $this->assertSame($children, $item->getItems());
    }

    public function testGetItemsWithNoItems()
    {
        $this->assertEmpty((new GroupedNavigationItem('Foo'))->getItems());
    }

    public function testCanAddItemToDropdown()
    {
        $group = new GroupedNavigationItem('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame([$child], $group->addItem($child)->getItems());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $group = new GroupedNavigationItem('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame($group, $group->addItem($child));
    }

    public function testCanAddMultipleItemsToDropdown()
    {
        $group = new GroupedNavigationItem('Foo');
        $items = $this->createNavigationItems();

        $this->assertSame($items, $group->addItems($items)->getItems());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $group = new GroupedNavigationItem('Foo');

        $this->assertSame($group, $group->addItems([]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $group = new GroupedNavigationItem('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Child', group: 'bar');

        $group->addItem($child);

        $this->assertSame('foo', $group->getGroupKey());
        $this->assertSame('bar', $child->getGroupKey());
    }

    public function testAddingAnItemWithNoGroupKeyUsesGroupIdentifier()
    {
        $group = new GroupedNavigationItem('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $group->addItem($child);

        $this->assertSame('foo', $group->getGroupKey());
        $this->assertSame('foo', $child->getGroupKey());
    }

    public function testGetPriorityUsesDefaultPriority()
    {
        $this->assertSame(999, (new GroupedNavigationItem('Foo'))->getPriority());
    }

    public function testGetPriorityWithNoChildrenUsesGroupPriority()
    {
        $this->assertSame(999, (new GroupedNavigationItem('Foo'))->getPriority());
    }

    public function testGetPriorityWithChildrenUsesGroupPriority()
    {
        $group = new GroupedNavigationItem('Foo', [new NavigationItem(new Route(new MarkdownPage()), 'Bar', 100)]);

        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityWithDocumentationPageChildrenUsesLowestPriority()
    {
        $items = [
            new NavigationItem(new Route(new DocumentationPage()), 'Foo', 100),
            new NavigationItem(new Route(new DocumentationPage()), 'Bar', 200),
            new NavigationItem(new Route(new DocumentationPage()), 'Baz', 300),
        ];

        $this->assertSame(100, (new GroupedNavigationItem('Foo', $items))->getPriority());
        $this->assertSame(100, (new GroupedNavigationItem('Foo', array_reverse($items)))->getPriority());
    }

    public function testGetPriorityUsesGroupPriorityForMixedChildTypes()
    {
        $group = new GroupedNavigationItem('Foo');

        foreach (HydeCoreExtension::getPageClasses() as $type) {
            $child = new NavigationItem(new Route(new $type()), 'Bar', 100);
            $group->addItem($child);
        }

        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityHandlesStringUrlChildGracefully()
    {
        $this->assertSame(999, (new GroupedNavigationItem('Foo', [new NavigationItem('foo', 'Bar', 100)]))->getPriority());
    }

    public function testGetPriorityHandlesExternalUrlChildGracefully()
    {
        $this->assertSame(999, (new GroupedNavigationItem('Foo', [new NavigationItem('https://example.com', 'Bar', 100)]))->getPriority());
    }

    public function testCreate()
    {
        $item = GroupedNavigationItem::create(new Route(new InMemoryPage('foo')));

        $this->assertInstanceOf(NavigationItem::class, $item);
        $this->assertNotInstanceOf(GroupedNavigationItem::class, $item);
        $this->assertSame(NavigationItem::class, $item::class);
    }

    public function testCreateWithLink()
    {
        $item = GroupedNavigationItem::create('foo', 'bar');

        $this->assertInstanceOf(NavigationItem::class, $item);
        $this->assertNotInstanceOf(GroupedNavigationItem::class, $item);
        $this->assertSame(NavigationItem::class, $item::class);
    }

    protected function createNavigationItems(): array
    {
        return [
            new NavigationItem(new Route(new InMemoryPage('foo')), 'Foo'),
            new NavigationItem(new Route(new InMemoryPage('bar')), 'Bar'),
        ];
    }
}
