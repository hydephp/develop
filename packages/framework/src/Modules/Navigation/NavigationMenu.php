<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteNotFoundException;
use Hyde\Framework\Modules\Routing\Router;
use Illuminate\Support\Collection;

class NavigationMenu extends Collection
{
    public Route $homeRoute;
    public Route $currentRoute;

    public function __construct()
    {
        $this->homeRoute = $this->getHomeRoute();

        parent::__construct();
    }

    public static function create(Route $currentRoute): static
    {
        return (new static())->setCurrentRoute($currentRoute)->generate()->sortItems();
    }

    public function setCurrentRoute(Route $currentRoute): self
    {
        $this->currentRoute = $currentRoute;

        return $this;
    }

    public function generate(): self
    {
        Router::getInstance()->getRoutes()->each(function (Route $route) {
            $item = $route->getSourceModel();
            if ($item instanceof NavigationMenuItemContract && $item->showInNavigation()) {
                $this->addItem($item);
            }
        });

        return $this;
    }

    public function sortItems(): self
    {
        $this->sortBy(function (NavigationMenuItemContract $item) {
            return $item->navigationMenuPriority();
        });

        return $this;
    }

    protected function addItem(NavigationMenuItemContract $item): void
    {
        if ($item->showInNavigation()) {
            $this->put($item->getRoute()->getRouteKey(), $item);
        }
    }

    protected function getHomeRoute(): Route
    {
        return Route::get('index') ?? throw new RouteNotFoundException('index');
    }
}
