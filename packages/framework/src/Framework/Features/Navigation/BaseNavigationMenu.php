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

    final public function __construct()
    {
        $this->items = new Collection();
    }

    public static function create(): static
    {
        return (new static())->generate()->filter()->sort();
    }

    /** @deprecated Will be made protected */
    public function generate(): static
    {
        Routes::each(function (Route $route): void {
            if (static::canAddRoute($route)) {
                $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
            }
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });

        return $this;
    }

    /** @deprecated Refactor to handle this upon generation */
    public function filter(): static
    {
        $this->items = $this->filterHiddenItems();
        $this->items = $this->filterDuplicateItems();

        return $this;
    }

    /** @deprecated Refactor to handle this upon generation */
    public function sort(): static
    {
        $this->items = $this->items->sortBy('priority')->values();

        return $this;
    }

    /** @deprecated Refactor to handle this upon generation */
    protected function filterHiddenItems(): Collection
    {
        return $this->items->reject(function (NavItem $item): bool {
            return $this->shouldItemBeHidden($item);
        })->values();
    }

    /** @deprecated Refactor to handle this upon generation */
    protected function filterDuplicateItems(): Collection
    {
        return $this->items->unique(function (NavItem $item): string {
            return $item->getGroup().$item->label;
        });
    }

    /** @deprecated Hidden items should not be added to start with */
    protected static function shouldItemBeHidden(NavItem $item): bool
    {
        return false;
    }

    protected static function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation();
    }
}
