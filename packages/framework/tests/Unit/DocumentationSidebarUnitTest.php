<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;
use Hyde\Support\Models\ExternalRoute;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 */
class DocumentationSidebarUnitTest extends UnitTestCase
{
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
        $item = $this->item('/', 'Home');

        $menu->add($item);

        $this->assertCount(1, $menu->getItems());
        $this->assertSame($item, $menu->getItems()->first());
    }

    public function testItemsAreInTheOrderTheyWereAddedWhenThereAreNoCustomPriorities()
    {
        $menu = new DocumentationSidebar();
        $item1 = $this->item('/', 'Home');
        $item2 = $this->item('/about', 'About');
        $item3 = $this->item('/contact', 'Contact');

        $menu->add($item1);
        $menu->add($item2);
        $menu->add($item3);

        $this->assertSame([$item1, $item2, $item3], $menu->getItems()->all());
    }

    public function testItemsAreSortedByPriority()
    {
        $menu = new DocumentationSidebar();
        $item1 = $this->item('/', 'Home', 100);
        $item2 = $this->item('/about', 'About', 200);
        $item3 = $this->item('/contact', 'Contact', 300);

        $menu->add($item3);
        $menu->add($item1);
        $menu->add($item2);

        $this->assertSame([$item1, $item2, $item3], $menu->getItems()->all());
    }

    protected function getItems(): array
    {
        return [
            $this->item('/', 'Home'),
            $this->item('/about', 'About'),
            $this->item('/contact', 'Contact'),
        ];
    }

    protected function item(string $destination, string $label, int $priority = 500): NavItem
    {
        return new NavItem(new ExternalRoute($destination), $label, $priority);
    }
}
