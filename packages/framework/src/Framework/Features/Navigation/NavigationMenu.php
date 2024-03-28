<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

use function Hyde\evaluate_arrayable;

/**
 * Represents a site navigation menu, and contains all of its navigation items.
 *
 * The automatic navigation menus are stored within the service container and can be resolved by their identifiers.
 *
 * @example `$menu = app('navigation.main');` for the main navigation menu.
 * @example `$menu = app('navigation.sidebar');` for the documentation sidebar.
 */
class NavigationMenu
{
    use HasNavigationItems;

    public const DEFAULT = 500;
    public const LAST = 999;

    public function __construct(Arrayable|array $items = [])
    {
        $this->items = new Collection();

        $this->add(evaluate_arrayable($items));
    }
}
