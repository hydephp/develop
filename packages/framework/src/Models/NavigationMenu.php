<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Router;
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
        return (new self())->setCurrentRoute($currentRoute ?? Hyde::currentRoute())->generate()->filter()->sort();
    }

    /**
     * @deprecated v0.50.0 - Automatically inferred from the view.
     */
    public function setCurrentRoute(RouteContract $currentRoute): self
    {
        $this->currentRoute = $currentRoute;

        return $this;
    }

    public function generate(): self
    {
        Router::getInstance()->getRoutes()->each(function (Route $route) {
            $this->items->push(NavItem::fromRoute($route));
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item) {
            $this->items->push($item);
        });

        return $this;
    }

    public function filter(): self
    {
        // Remove hidden items
        $this->items = $this->items->reject(function (NavItem $item) {
            return $item->hidden;
        })->values();

        // Remove duplicate items
        $this->items = $this->items->unique(function (NavItem $item) {
            return $item->resolveLink();
        });

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
}
