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
}
