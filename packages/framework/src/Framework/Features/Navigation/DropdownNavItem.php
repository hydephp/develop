<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;

use function collect;

/**
 * A navigation item that contains other navigation items.
 *
 * Unlike a regular navigation items, a dropdown item does not have a route or URL destination.
 *
 * @see \Hyde\Framework\Testing\Unit\DropdownNavItemTest
 */
class DropdownNavItem extends NavItem
{
    /** @var array<NavItem> */
    public array $items;

    /** @param array<NavItem> $items */
    public function __construct(string $label, array $items)
    {
        parent::__construct('', $label, 999);
        $this->items = $items;
    }

    public static function fromArray(string $name, array $items): static
    {
        return new static($name, $items);
    }

    public function getItems(): Collection
    {
        return collect($this->items);
    }
}
