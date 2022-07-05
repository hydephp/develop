<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\Router;
use Illuminate\Support\Collection;

class NavigationMenu extends Collection
{
    protected Route $route;

    public function __construct(Route $route)
    {
        $this->route = $route;

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
}
