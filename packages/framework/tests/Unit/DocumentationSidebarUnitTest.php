<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;
use Hyde\Support\Models\ExternalRoute;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\NavGroupItem;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 * @see \Hyde\Framework\Testing\Unit\NavigationMenuUnitTest
 */
class DocumentationSidebarUnitTest extends UnitTestCase
{
    // Base menu tests

    public function testCanConstruct()
    {
        $this->assertInstanceOf(DocumentationSidebar::class, new DocumentationSidebar());
    }

    public function testCanConstructWithItemsArray()
    {
        $this->assertInstanceOf(DocumentationSidebar::class, new DocumentationSidebar($this->getItems()));
    }

    public function testCanConstructWithItemsArrayable()
    {
        $this->assertInstanceOf(DocumentationSidebar::class, new DocumentationSidebar(collect($this->getItems())));
    }

    public function testGetItemsReturnsCollection()
    {
        $this->assertInstanceOf(Collection::class, (new DocumentationSidebar())->getItems());
    }

    public function testGetItemsReturnsCollectionWhenSuppliedArray()
    {
        $this->assertInstanceOf(Collection::class, (new DocumentationSidebar($this->getItems()))->getItems());
    }

    public function testGetItemsReturnsCollectionWhenSuppliedArrayable()
    {
        $this->assertInstanceOf(Collection::class, (new DocumentationSidebar(collect($this->getItems())))->getItems());
    }

    public function testGetItemsReturnsItems()
    {
        $items = $this->getItems();

        $this->assertSame($items, (new DocumentationSidebar($items))->getItems()->all());
    }

    public function testGetItemsReturnsItemsWhenSuppliedArrayable()
    {
        $items = $this->getItems();

        $this->assertSame($items, (new DocumentationSidebar(collect($items)))->getItems()->all());
    }

    public function testGetItemsReturnsEmptyArrayWhenNoItems()
    {
        $this->assertSame([], (new DocumentationSidebar())->getItems()->all());
    }

    public function testCanAddItems()
    {
        $menu = new DocumentationSidebar();

        $item = $this->item('/', 'Docs');

        $menu->add($item);

        $this->assertCount(1, $menu->getItems());
        $this->assertSame($item, $menu->getItems()->first());
    }

    public function testItemsAreInTheOrderTheyWereAddedWhenThereAreNoCustomPriorities()
    {
        $menu = new DocumentationSidebar();

        $item1 = $this->item('/', 'Docs');
        $item2 = $this->item('/installation', 'Installation');
        $item3 = $this->item('/getting-started', 'Getting Started');

        $menu->add($item1);
        $menu->add($item2);
        $menu->add($item3);

        $this->assertSame([$item1, $item2, $item3], $menu->getItems()->all());
    }

    public function testItemsAreSortedByPriority()
    {
        $menu = new DocumentationSidebar();

        $item1 = $this->item('/', 'Docs', 100);
        $item2 = $this->item('/installation', 'Installation', 200);
        $item3 = $this->item('/getting-started', 'Getting Started', 300);

        $menu->add($item3);
        $menu->add($item1);
        $menu->add($item2);

        $this->assertSame([$item1, $item2, $item3], $menu->getItems()->all());
    }

    // Sidebar specific tests

    public function testGetMethodResolvesInstanceFromServiceContainer()
    {
        app()->instance('navigation.sidebar', $instance = new DocumentationSidebar());

        $this->assertSame($instance, DocumentationSidebar::get());
    }

    public function testGetHeaderReturnsDefaultWhenNotConfigured()
    {
        self::mockConfig();

        $this->assertSame('Documentation', (new DocumentationSidebar())->getHeader());
    }

    public function testGetHeaderReturnsConfiguredValue()
    {
        self::mockConfig(['docs.sidebar.header' => 'Some header']);

        $this->assertSame('Some header', (new DocumentationSidebar())->getHeader());
    }

    public function testGetFooterReturnsBackLinkByDefault()
    {
        self::mockConfig();

        $this->assertSame('[Back to home page](../)', (new DocumentationSidebar())->getFooter());
    }

    public function testGetFooterReturnsStringWhenConfigIsString()
    {
        self::mockConfig(['docs.sidebar.footer' => 'Some footer content']);

        $this->assertSame('Some footer content', (new DocumentationSidebar())->getFooter());
    }

    public function testIsCollapsibleReturnsTrueByDefault()
    {
        self::mockConfig();

        $this->assertTrue((new DocumentationSidebar())->isCollapsible());
    }

    public function testIsCollapsibleReturnsTrueWhenConfigIsTrue()
    {
        self::mockConfig(['docs.sidebar.collapsible' => true]);

        $this->assertTrue((new DocumentationSidebar())->isCollapsible());
    }

    public function testIsCollapsibleReturnsFalseWhenConfigIsFalse()
    {
        self::mockConfig(['docs.sidebar.collapsible' => false]);

        $this->assertFalse((new DocumentationSidebar())->isCollapsible());
    }

    public function testHasFooterReturnsTrueByDefault()
    {
        self::mockConfig();

        $this->assertTrue((new DocumentationSidebar())->hasFooter());
    }

    public function testHasFooterReturnsTrueWhenConfigIsString()
    {
        self::mockConfig(['docs.sidebar.footer' => 'Some footer content']);

        $this->assertTrue((new DocumentationSidebar())->hasFooter());
    }

    public function testHasGroupsReturnsFalseWhenNoItemsHaveChildren()
    {
        $this->assertFalse((new DocumentationSidebar())->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenAtLeastOneItemIsNavGroupItemInstance()
    {
        self::mockConfig();
        self::setupKernel();

        $menu = new DocumentationSidebar([
            new NavGroupItem('foo', []),
        ]);

        $this->assertTrue($menu->hasGroups());
    }

    protected function getItems(): array
    {
        return [
            $this->item('/', 'Docs'),
            $this->item('/installation', 'Installation'),
            $this->item('/getting-started', 'Getting Started'),
        ];
    }

    protected function item(string $destination, string $label, int $priority = 500): NavItem
    {
        return new NavItem(new ExternalRoute($destination), $label, $priority);
    }
}
