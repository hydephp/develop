<?php

declare(strict_types=1);

namespace Hyde\Framework\Models\Navigation;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Support\Route;
use Hyde\Hyde;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 * @phpstan-consistent-constructor
 */
class NavigationMenu
{
    public Route $currentRoute;

    public Collection $items;

    public function __construct()
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
        Hyde::routes()->each(function (Route $route) {
            $this->items->push(NavItem::fromRoute($route));
        });

        collect(config('hyde.navigation.custom', []))->each(function (NavItem $item) {
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
        return $this->items->reject(function (NavItem $item) {
            return $item->hidden || $this->filterDocumentationPage($item);
        })->values();
    }

    protected function filterDuplicateItems(): Collection
    {
        return $this->items->unique(function (NavItem $item) {
            return $item->resolveLink();
        });
    }

    protected function filterDocumentationPage(NavItem $item): bool
    {
        return isset($item->route)
            && $item->route->getSourceModel() instanceof DocumentationPage
            && $item->route->getRouteKey() !== 'docs/index';
    }
}
