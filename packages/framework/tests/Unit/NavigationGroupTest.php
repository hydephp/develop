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
use Hyde\Framework\Features\Navigation\NavigationGroup;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationGroup
 */
class NavigationGroupTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testCanConstruct()
    {
        $this->assertSame('Foo', (new NavigationGroup('Foo'))->getLabel());
    }

    public function testCanConstructWithPriority()
    {
        $this->assertSame(500, (new NavigationGroup('Foo', priority: 500))->getPriority());
    }

    public function testDefaultPriorityValueIsLast()
    {
        $this->assertSame(999, (new NavigationGroup('Foo'))->getPriority());
    }

    public function testCanConstructWithChildren()
    {
        $children = $this->createNavigationItems();
        $item = new NavigationGroup('Foo', $children);

        $this->assertCount(2, $item->getItems());
        $this->assertSame($children, $item->getItems());
    }

    public function testCanConstructWithChildrenWithoutRoute()
    {
        $children = $this->createNavigationItems();
        $item = new NavigationGroup('Foo', $children);

        $this->assertCount(2, $item->getItems());
        $this->assertSame($children, $item->getItems());
    }

    public function testIsInstanceOfNavigationElement()
    {
        $this->assertInstanceOf(NavigationGroup::class, new NavigationGroup('Foo'));
    }

    public function testGetItems()
    {
        $children = $this->createNavigationItems();
        $item = new NavigationGroup('Foo', $children);

        $this->assertSame($children, $item->getItems());
    }

    public function testGetItemsWithNoItems()
    {
        $this->assertEmpty((new NavigationGroup('Foo'))->getItems());
    }

    public function testCanAddItemToDropdown()
    {
        $group = new NavigationGroup('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame([$child], $group->addItem($child)->getItems());
    }

    public function testAddChildMethodReturnsSelf()
    {
        $group = new NavigationGroup('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $this->assertSame($group, $group->addItem($child));
    }

    public function testCanAddMultipleItemsToDropdown()
    {
        $group = new NavigationGroup('Foo');
        $items = $this->createNavigationItems();

        $this->assertSame($items, $group->addItems($items)->getItems());
    }

    public function testAddChildrenMethodReturnsSelf()
    {
        $group = new NavigationGroup('Foo');

        $this->assertSame($group, $group->addItems([]));
    }

    public function testAddingAnItemWithAGroupKeyKeepsTheSetGroupKey()
    {
        $group = new NavigationGroup('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Child', group: 'bar');

        $group->addItem($child);

        $this->assertSame('bar', $child->getGroupKey());
    }

    public function testAddingAnItemWithNoGroupKeyDoesNotModifyGroupIdentifier()
    {
        $group = new NavigationGroup('Foo');
        $child = new NavigationItem(new Route(new MarkdownPage()), 'Bar');

        $group->addItem($child);

        $this->assertNull($child->getGroupKey());
    }

    public function testGetPriorityUsesDefaultPriority()
    {
        $this->assertSame(999, (new NavigationGroup('Foo'))->getPriority());
    }

    public function testGetPriorityWithNoChildrenUsesGroupPriority()
    {
        $this->assertSame(999, (new NavigationGroup('Foo'))->getPriority());
    }

    public function testGetPriorityWithChildrenUsesGroupPriority()
    {
        $group = new NavigationGroup('Foo', [new NavigationItem(new Route(new MarkdownPage()), 'Bar', 100)]);

        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityWithDocumentationPageChildrenUsesLowestPriority()
    {
        $items = [
            new NavigationItem(new Route(new DocumentationPage()), 'Foo', 100),
            new NavigationItem(new Route(new DocumentationPage()), 'Bar', 200),
            new NavigationItem(new Route(new DocumentationPage()), 'Baz', 300),
        ];

        $this->assertSame(100, (new NavigationGroup('Foo', $items))->getPriority());
        $this->assertSame(100, (new NavigationGroup('Foo', array_reverse($items)))->getPriority());
    }

    public function testGetPriorityUsesGroupPriorityForMixedChildTypes()
    {
        $group = new NavigationGroup('Foo');

        foreach (HydeCoreExtension::getPageClasses() as $type) {
            $child = new NavigationItem(new Route(new $type()), 'Bar', 100);
            $group->addItem($child);
        }

        $this->assertSame(999, $group->getPriority());
    }

    public function testGetPriorityHandlesStringUrlChildGracefully()
    {
        $this->assertSame(999, (new NavigationGroup('Foo', [new NavigationItem('foo', 'Bar', 100)]))->getPriority());
    }

    public function testGetPriorityHandlesExternalUrlChildGracefully()
    {
        $this->assertSame(999, (new NavigationGroup('Foo', [new NavigationItem('https://example.com', 'Bar', 100)]))->getPriority());
    }

    public function testCreate()
    {
        $this->assertEquals(
            new NavigationGroup('Foo', [new NavigationItem(new Route(new InMemoryPage('foo')), 'Foo')], 100),
            NavigationGroup::create('Foo', [new NavigationItem(new Route(new InMemoryPage('foo')), 'Foo')], 100)
        );
    }

    protected function createNavigationItems(): array
    {
        return [
            new NavigationItem(new Route(new InMemoryPage('foo')), 'Foo'),
            new NavigationItem(new Route(new InMemoryPage('bar')), 'Bar'),
        ];
    }
}
