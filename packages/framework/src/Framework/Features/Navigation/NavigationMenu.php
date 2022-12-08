<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use BadMethodCallException;
use function config;
use Hyde\Foundation\Facades\Router;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use Illuminate\Support\Collection;
use function in_array;

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
        Router::each(function (Route $route): void {
            $this->items->push(NavItem::fromRoute($route));
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
            return $item->hidden || $this->filterDocumentationPage($item);
        })->values();
    }

    protected function filterDuplicateItems(): Collection
    {
        return $this->items->unique(function (NavItem $item): string {
            return $item->resolveLink();
        });
    }

    protected function filterDocumentationPage(NavItem $item): bool
    {
        return isset($item->route)
            && $item->route->getPage() instanceof DocumentationPage
            && $item->route->getRouteKey() !== 'docs/index';
    }

    public function hasDropdowns(): bool
    {
        if (config('hyde.navigation.subdirectories', 'hidden') !== 'dropdown') {
            return false;
        }

        return $this->items->contains(function (NavItem $item): bool {
            return $this->canBeInDropdown($item);
        });
    }

    /**
     * @return array<string, array<NavItem>>
     */
    public function getDropdowns(): array
    {
        if (config('hyde.navigation.subdirectories', 'hidden') !== 'dropdown') {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        $dropdowns = [];

        /** @var \Hyde\Framework\Features\Navigation\NavItem $item */
        foreach ($this->items as $item) {
            if (! $this->canBeInDropdown($item)) {
                continue;
            }

            $dropdowns[$item->getGroup()][] = $item;
        }

        return $dropdowns;
    }

    protected static function canBeInDropdown(NavItem $item): bool
    {
        return ($item->getGroup() !== null) && ! in_array($item->route->getPageClass(), [DocumentationPage::class, MarkdownPost::class]);
    }
}
