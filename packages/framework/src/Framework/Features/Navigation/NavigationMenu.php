<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents a site navigation menu, and contains all of its navigation items.
 */
class NavigationMenu
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    /**
     * Create a new navigation menu instance.
     */
    public function __construct(Arrayable|array $items = [])
    {
        $this->items = new Collection();

        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Get the navigation items in the menu.
     *
     * Items are automatically sorted by their priority, falling back to the order they were added.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem>
     */
    public function getItems(): Collection
    {
        return $this->items->sortBy('priority')->values();
    }

    /**
     * Add a navigation item to the navigation menu.
     */
    public function add(NavItem $item): void
    {
        $this->items->push($item);
    }
}
