<?php

namespace Hyde\Framework\Modules\Navigation;

use Hyde\Framework\Modules\Routing\Route;
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
        return new static($currentRoute);
    }
}
