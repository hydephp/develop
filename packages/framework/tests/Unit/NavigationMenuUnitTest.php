<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\Navigation\NavigationMenu;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 *
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
class NavigationMenuUnitTest extends UnitTestCase
{
    public function testCanConstruct()
    {
        $this->assertInstanceOf(NavigationMenu::class, new NavigationMenu());
    }

    public function testCanConstructWithItemsArray()
    {
        $this->assertInstanceOf(NavigationMenu::class, new NavigationMenu([
            'item1' => 'value1',
            'item2' => 'value2',
        ]));
    }

    public function testCanConstructWithItemsArrayable()
    {
        $this->assertInstanceOf(NavigationMenu::class, new NavigationMenu(collect([
            'item1' => 'value1',
            'item2' => 'value2',
        ])));
    }

    public function testGetItemsReturnsCollection()
    {
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, (new NavigationMenu())->getItems());
    }

    public function testGetItemsReturnsCollectionWhenSuppliedArray()
    {
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, (new NavigationMenu([
            'item1' => 'value1',
            'item2' => 'value2',
        ]))->getItems());
    }

    public function testGetItemsReturnsCollectionWhenSuppliedArrayable()
    {
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, (new NavigationMenu(collect([
            'item1' => 'value1',
            'item2' => 'value2',
        ])))->getItems());
    }

    public function testGetItemsReturnsItems()
    {
        $items = [
            'item1' => 'value1',
            'item2' => 'value2',
        ];
        $this->assertSame($items, (new NavigationMenu($items))->getItems()->all());
    }

    public function testGetItemsReturnsItemsWhenSuppliedArrayable()
    {
        $items = [
            'item1' => 'value1',
            'item2' => 'value2',
        ];
        $this->assertSame($items, (new NavigationMenu(collect($items)))->getItems()->all());
    }

    public function testGetItemsReturnsEmptyArrayWhenNoItems()
    {
        $this->assertSame([], (new NavigationMenu())->getItems()->all());
    }
}
