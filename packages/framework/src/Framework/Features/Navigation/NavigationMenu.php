<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use BadMethodCallException;
use function collect;
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
    public Collection $items;

    /** @var array<string, array<NavItem>> */
    protected array $dropdowns;

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

        if ($this->dropdownsEnabled()) {
            $this->dropdowns = $this->makeDropdowns();
        }

        return $this;
    }

    /** @return $this */
    public function filter(): static
    {
        $this->items = $this->filterHiddenItems();
        $this->items = $this->filterDuplicateItems();

        if ($this->dropdownsEnabled()) {
            $this->items = $this->filterDropdownItems();
        }

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

    protected function filterDropdownItems(): Collection
    {
        $dropdownItems = collect($this->getDropdowns())->flatten()->toArray();

        return $this->items->reject(function (NavItem $item) use ($dropdownItems): bool {
            return in_array($item, $dropdownItems);
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
        if (! $this->dropdownsEnabled()) {
            return false;
        }

        return count($this->getDropdowns()) >= 1;
    }

    /** @return array<string, array<NavItem>> */
    public function getDropdowns(): array
    {
        if (! $this->dropdownsEnabled()) {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        return $this->dropdowns;
    }

    protected static function canBeInDropdown(NavItem $item): bool
    {
        return ($item->getGroup() !== null) && ! in_array($item->route->getPageClass(), [DocumentationPage::class, MarkdownPost::class]);
    }

    protected static function dropdownsEnabled(): bool
    {
        return config('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }

    protected function makeDropdowns(): array
    {
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

    /**
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
