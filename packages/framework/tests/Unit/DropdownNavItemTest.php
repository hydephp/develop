<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Navigation\DropdownNavItem
 */
class DropdownNavItemTest extends TestCase
{
    public function testConstruct()
    {
        $item = new DropdownNavItem('foo', []);

        $this->assertSame('foo', $item->name);
        $this->assertSame([], $item->items);
    }

    public function testFromArray()
    {
        $item = DropdownNavItem::fromArray('foo', []);

        $this->assertSame('foo', $item->name);
        $this->assertSame([], $item->items);
    }
}
