<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * A navigation item that contains other navigation items.
 *
 * Unlike a regular navigation items, a dropdown item does not have a route or URL destination.
 */
class DropdownNavItem extends NavItem
{
    /** @var array<NavItem> */
    public array $items;
    public string $name;
    public string $href = '#';

    public function __construct(string $name, array $items)
    {
        parent::__construct(null, $name, 999);
        $this->items = $items;
        $this->name = $name;
    }

    public static function fromArray(string $name, array $items): static
    {
        return new static($name, $items);
    }
}
