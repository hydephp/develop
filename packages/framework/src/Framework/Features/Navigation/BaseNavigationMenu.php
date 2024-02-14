<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

/** @deprecated Use the new NavigationMenu class instead */
abstract class BaseNavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    public function __construct(Arrayable|array $items = [])
    {
        $this->items = new Collection($items);
    }

    public static function create(): static
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();
        $menu->removeDuplicateItems();

        return $menu;
    }

    /** @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    public function getItems(): Collection
    {
        return $this->items->values();
    }
}
