<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\RouteNotFoundException;
use Hyde\Framework\Modules\Routing\Router;
use Illuminate\Support\Collection;

class NavigationMenu extends Collection
{
    protected Route $homeRoute;
    protected Route $currentRoute;

    public function __construct(Route $currentRoute)
    {
        $this->currentRoute = $currentRoute;
        $this->homeRoute = $this->getHomeRoute();

        parent::__construct();
    }

    public static function create(Route $currentRoute): static
    {
        return (new static($currentRoute))->generate();
    }

    public function generate(): self
    {
        Router::getInstance()->getRoutes()->each(function (Route $route) {
            $this->addItem($route);
        });

        return $this;
    }

    protected function addItem(Route $route): void
    {
        if ($route instanceof NavigationMenuItemContract && $route->showInNavigation()) {
            $this->put($route->getRouteKey(), $route);
        }
    }

    protected function getHomeRoute(): Route
    {
        return Route::get('index') ?? throw new RouteNotFoundException('index');
    }
}
