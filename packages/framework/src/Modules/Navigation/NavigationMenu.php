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
        return (new static())->setCurrentRoute($currentRoute)->generate();
    }

    public function setCurrentRoute(Route $currentRoute): self
    {
        $this->currentRoute = $currentRoute;

        return $this;
    }

    public function generate(): self
    {
        Router::getInstance()->getRoutes()->sortBy(function (Route $route) {
            return $route->getSourceModel()->navigationMenuPriority();
        })->each(function (Route $route) {
            if ($route->getSourceModel()->showInNavigation()) {
                $this->push($route);
            }
        });

        return $this;
    }

    protected function getHomeRoute(): Route
    {
        return Route::get('index') ?? throw new RouteNotFoundException('index');
    }
}
