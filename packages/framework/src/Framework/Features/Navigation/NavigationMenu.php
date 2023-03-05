<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use BadMethodCallException;
use function in_array;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
class NavigationMenu extends BaseNavigationMenu
{
    /** @deprecated Will be made protected */
    public function generate(): static
    {
        parent::generate();

        if ($this->dropdownsEnabled()) {
            $this->createDropdownsForGroupedItems();
        }

        return $this;
    }

    protected function createDropdownsForGroupedItems(): void
    {
        $dropdowns = [];

        /** @var \Hyde\Framework\Features\Navigation\NavItem $item */
        foreach ($this->items as $item) {
            if ($this->canAddItemToDropdown($item)) {
                // Buffer the item in the dropdowns array
                $dropdowns[$item->getGroup()][] = $item;

                // Remove the item from the main items collection
                $this->items->forget($item->route->getRouteKey());
            }
        }

        foreach ($dropdowns as $group => $items) {
            // Create a new dropdown item containing the buffered items
            $this->items->put("dropdown.$group", new DropdownNavItem($group, $items));
        }
    }

    public function hasDropdowns(): bool
    {
        if (! $this->dropdownsEnabled()) {
            return false;
        }

        return count($this->getDropdowns()) >= 1;
    }

    /** @return array<string, DropdownNavItem> */
    public function getDropdowns(): array
    {
        if (! $this->dropdownsEnabled()) {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        return $this->items->filter(function (NavItem $item): bool {
            return $item instanceof DropdownNavItem;
        })->all();
    }

    protected function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route) && (! $route->getPage() instanceof DocumentationPage || $route->is(DocumentationPage::homeRouteName()));
    }

    protected function canAddItemToDropdown(NavItem $item): bool
    {
        return ($item->getGroup() !== null) && ! in_array($item->route->getPageClass(), [DocumentationPage::class, MarkdownPost::class]);
    }

    protected function dropdownsEnabled(): bool
    {
        return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }
}
