<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Contains shared logic for classes that have navigation items.
 *
 * @see \Hyde\Framework\Features\Navigation\NavigationMenu
 * @see \Hyde\Framework\Features\Navigation\NavigationGroup
 */
trait HasNavigationItems
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup> */
    protected Collection $items;

    /**
     * Get the navigation items in the menu.
     *
     * Items are automatically sorted by their priority, falling back to the order they were added.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup>
     */
    public function getItems(): Collection
    {
        // The reason we sort them here is that navigation items can be added from different sources,
        // so any sorting we do in generator actions will only be partial. This way, we can ensure
        // that the items are always freshly sorted by their priorities when they are retrieved.

        return $this->items->sortBy(fn (NavigationItem|NavigationGroup $item) => $item->getPriority())->values();
    }

    /**
     * Add one or more navigation items to the navigation menu.
     *
     * @param  \Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup|array<\Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup>  $items
     */
    public function add(NavigationItem|NavigationGroup|array $items): static
    {
        foreach (Arr::wrap($items) as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    protected function addItem(NavigationItem|NavigationGroup $item): void
    {
        $this->items->push($item);
    }
}
