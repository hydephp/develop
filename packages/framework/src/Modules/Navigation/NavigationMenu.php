<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Models\NavItem;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\Router;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
class NavigationMenu
{
    public RouteContract $homeRoute;
    public RouteContract $currentRoute;

    public Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
        $this->homeRoute = $this->getHomeRoute();
    }

    public static function create(RouteContract $currentRoute): static
    {
        return (new static())->setCurrentRoute($currentRoute)->generate()->filter()->sort();
    }

    public function setCurrentRoute(RouteContract $currentRoute): self
    {
        $this->currentRoute = $currentRoute;

        return $this;
    }

    public function generate(): self
    {
        Router::getInstance()->getRoutes()->each(function (Route $route) {
            $this->items->push(NavItem::toRoute($route));
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

    /** @internal */
    public function getHomeRoute(): Route
    {
        return Route::get('index') ?? Route::get('404') ?? new Route(new MarkdownPage);
    }
}
