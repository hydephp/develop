<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;

use function collect;

/**
 * A navigation item that contains other navigation items.
 *
 * @deprecated Use the `NavItem::getChildren()` instead.
 *
 * Unlike a regular navigation items, a dropdown item does not have a route or URL destination.
 */
class DropdownNavItem extends NavItem
{
    /** @var array<NavItem> */
    public array $items;

    /** @param array<NavItem> $items */
    public function __construct(string $label, array $items, ?int $priority = null)
    {
        parent::__construct('', $label, $priority ?? static::searchForDropdownPriorityInNavigationConfig($label) ?? 999);
        $this->items = $items;
    }

    /** @param array<NavItem> $items */
    public static function fromArray(string $name, array $items): static
    {
        return new static($name, $items);
    }

    /** @return Collection<NavItem> */
    public function getItems(): Collection
    {
        return collect($this->items);
    }
}
