<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Services\RoutingService;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
class NavigationMenu
{
    public RouteContract $currentRoute;

    public Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public static function create(): static
    {
        return (new static())->generate()->filter()->sort();
    }

    /** @return $this */
    public function generate(): static
    {
        RoutingService::getInstance()->getRoutes()->each(function (Route $route) {
            $this->items->push(NavItem::fromRoute($route));
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item) {
            $this->items->push($item);
        });

        return $this;
    }

    /** @return $this */
    public function filter(): static
    {
        $this->items = $this->filterHiddenItems();
        $this->items = $this->filterDuplicateItems();

        return $this;
    }

    /** @return $this */
    public function sort(): static
    {
        $this->items = $this->items->sortBy('priority')->values();

        return $this;
    }

    /** @deprecated v0.50.x - use Route::home() instead */
    public function getHomeLink(): string
    {
        return Route::get('index');
    }

    protected function filterHiddenItems(): Collection
    {
        return $this->items->reject(function (NavItem $item) {
            return $item->hidden;
        })->values();
    }

    protected function filterDuplicateItems(): Collection
    {
        return $this->items->unique(function (NavItem $item) {
            return $item->resolveLink();
        });
    }
}
