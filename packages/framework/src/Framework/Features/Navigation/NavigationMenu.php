<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use BadMethodCallException;

/**
 */
class NavigationMenu extends BaseNavigationMenu
{
    protected function generate(): void
    {
        parent::generate();

        if ($this->dropdownsEnabled()) {
            $this->moveGroupedItemsIntoDropdowns();
        }
    }

    public function hasDropdowns(): bool
    {
        return $this->dropdownsEnabled() && count($this->getDropdowns()) >= 1;
    }

    /** @return array<string, DropdownNavItem> */
    public function getDropdowns(): array
    {
        if (! $this->dropdownsEnabled()) {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        return $this->items->filter(function (NavItem $item): bool {
            return $item instanceof DropdownNavItem;
        })->values()->all();
    }

    protected function moveGroupedItemsIntoDropdowns(): void
    {
        $dropdowns = [];

        foreach ($this->items as $key => $item) {
            if ($this->canAddItemToDropdown($item)) {
                // Buffer the item in the dropdowns array
                $dropdowns[$item->getGroup()][] = $item;

                // Remove the item from the main items collection
                $this->items->forget($key);
            }
        }

        foreach ($dropdowns as $group => $items) {
            // Create a new dropdown item containing the buffered items
            $this->items->add(new DropdownNavItem($group, $items));
        }
    }

    protected function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route) && (! $route->getPage() instanceof DocumentationPage || $route->is(DocumentationPage::homeRouteName()));
    }

    protected function canAddItemToDropdown(NavItem $item): bool
    {
        return $item->getGroup() !== null;
    }

    protected function dropdownsEnabled(): bool
    {
        return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }
}
