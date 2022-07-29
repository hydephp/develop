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

    public static function create(?RouteContract $currentRoute = null): static
    {
        return (new static())->generate()->filter()->sort();
    }

    public function generate(): self
    {
        RoutingService::getInstance()->getRoutes()->each(function (Route $route) {
            $this->items->push(NavItem::fromRoute($route));
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item) {
            $this->items->push($item);
        });

        return $this;
    }

    public function filter(): self
    {
        $this->items = $this->filterHiddenItems();
        $this->items = $this->filterDuplicateItems();

        return $this;
    }

    public function sort(): self
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
