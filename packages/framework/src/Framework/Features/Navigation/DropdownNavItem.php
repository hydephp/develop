<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\BladePage;
use Hyde\Support\Models\Route;

class DropdownNavItem extends NavItem
{
    /** @var array<NavItem> */
    public array $items;
    public string $name;
    public string $href = '#';

    public function __construct(string $name, array $items)
    {
        parent::__construct(self::route(), $name);
        $this->items = $items;
        $this->name = $name;
    }

    protected static function route(): Route
    {
        return new Route(new BladePage());
    }
}
