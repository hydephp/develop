<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\Route;
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
            $this->addLink($route);
        });


        return $this;
    }

    protected function addLink(Route $route): void
    {
        if ($this->isRouteHidden($route)) {
            return;
        }

        $this->put($route->getRouteKey(), $route);
    }

    protected function isRouteHidden(Route $route): bool
    {
        return $this->hasHiddenProperty($route->getSourceModel())
            || ($route->getSourceModel() instanceof DocumentationPage)
            || ($route->getSourceModel() instanceof MarkdownPost);
    }

    protected function hasHiddenProperty(PageContract $page): bool
    {
        return ($page instanceof AbstractMarkdownPage) && $page->markdown()->matter('hidden');
    }

    protected function getHomeRoute(): Route
    {
        return Route::get('index') ?? throw new RouteNotFoundException('index');
    }
}
