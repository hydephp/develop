<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Modules\Routing\Route;

class NavigationMenu
{
    protected Route $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public static function create(Route $currentRoute): static
    {
        return new static($currentRoute);
    }
}
