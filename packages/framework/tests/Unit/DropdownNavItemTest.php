<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
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

    public function testWithChildren()
    {
        $children = [
            new NavItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = new DropdownNavItem('foo', $children);
        $this->assertSame($children, $item->items);

        $item = DropdownNavItem::fromArray('foo', $children);
        $this->assertSame($children, $item->items);
    }
}
