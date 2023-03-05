<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use function collect;
use function config;
use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\Route;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
abstract class BaseNavigationMenu
{
    public Collection $items;

    /** @todo Consider protecting to scope down public API */
    final public function __construct()
    {
        $this->items = new Collection();
    }

    public static function create(): static
    {
        return (new static())->generate()->filterDuplicateItems()->sortByPriority();
    }

    /** @deprecated Will be made protected */
    public function generate(): static
    {
        Routes::each(function (Route $route): void {
            if ($this->canAddRoute($route)) {
                $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
            }
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });

        return $this;
    }

    protected function filterDuplicateItems(): static
    {
        $this->items = $this->items->unique(function (NavItem $item): string {
            return $item->getGroup() . $item->label;
        });

        return $this;
    }

    protected function sortByPriority(): static
    {
        $this->items = $this->items->sortBy('priority')->values();

        return $this;
    }

    protected function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation();
    }
}
