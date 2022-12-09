<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use function collect;
use function config;
use Hyde\Foundation\Facades\Router;
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

    /** @return $this */
    public function generate(): static
    {
        Router::each(function (Route $route): void {
            $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            $this->items->push($item);
        });

        return $this;
    }

    /** @return $this */
    public function filter(): static
    {
        $this->items = $this->filterHiddenItems();
        $this->items = $this->filterDuplicateItems();

        return $this;
    }

    /** @return $this */
    public function sort(): static
    {
        $this->items = $this->items->sortBy('priority')->values();

        return $this;
    }

    protected function filterHiddenItems(): Collection
    {
        return $this->items->reject(function (NavItem $item): bool {
            return $this->shouldItemBeHidden($item);
        })->values();
    }

    protected function filterDuplicateItems(): Collection
    {
        return $this->items->unique(function (NavItem $item): string {
            return $item->label;
        });
    }

    protected static function shouldItemBeHidden(NavItem $item): bool
    {
        return $item->hidden;
    }
}
