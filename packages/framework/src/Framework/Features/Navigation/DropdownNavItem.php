<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * A navigation item that contains other navigation items.
 *
 * @deprecated Use the `NavItem::getChildren()` instead.
 *
 * Unlike a regular navigation items, a dropdown item does not have a route or URL destination.
 */
class DropdownNavItem extends NavItem
{
    /** @deprecated */
    public array $items;

    /** @param array<NavItem> $items */
    public function __construct(string $label, array $items, ?int $priority = null)
    {
        parent::__construct('', $label, $priority ?? static::searchForDropdownPriorityInNavigationConfig($label) ?? 999, children: $items);
        $this->items = $items;
    }
}
