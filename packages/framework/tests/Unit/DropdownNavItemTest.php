<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Config;
use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Features\Navigation\DropdownNavItem
 */
class DropdownNavItemTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Render::swap(new RenderData());
    }

    public static function tearDownAfterClass(): void
    {
        Render::swap(new RenderData());
    }

    public function testDropdownConstruct()
    {
        $item = new DropdownNavItem('foo', []);

        $this->assertSame('foo', $item->label);
        $this->assertSame([], $item->items);
        $this->assertSame(999, $item->priority);
    }

    public function testDropdownConstructWithCustomPriority()
    {
        $item = new DropdownNavItem('foo', [], 500);

        $this->assertSame(500, $item->priority);
    }

    public function testDropdownConstructWithNullPriority()
    {
        $item = new DropdownNavItem('foo', [], null);

        $this->assertSame(999, $item->priority);
    }

    public function testDropdownFromArray()
    {
        $item = DropdownNavItem::fromArray('foo', []);

        $this->assertSame('foo', $item->label);
        $this->assertSame([], $item->items);
    }

    public function testDropdownWithChildren()
    {
        $children = [
            new NavItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = new DropdownNavItem('foo', $children);
        $this->assertSame($children, $item->items);

        $item = DropdownNavItem::fromArray('foo', $children);
        $this->assertSame($children, $item->items);
    }

    public function testDropdownGetItems()
    {
        $children = [
            new NavItem(new Route(new MarkdownPage()), 'bar'),
        ];

        $item = new DropdownNavItem('foo', $children);
        $this->assertSame($children, $item->getItems()->all());

        $item = DropdownNavItem::fromArray('foo', $children);
        $this->assertSame($children, $item->getItems()->all());
    }

    public function testCanSetDropdownPriorityInConfig()
    {
        $root = Config::getFacadeRoot();
        $mock = clone $root;
        Config::swap($mock);

        Config::set('hyde.navigation.order.foo', 500);
        $item = new DropdownNavItem('foo', []);

        $this->assertSame(500, $item->priority);

        Config::swap($root);
    }
}
