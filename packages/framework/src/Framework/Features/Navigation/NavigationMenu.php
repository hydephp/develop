<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;

class NavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }
}
