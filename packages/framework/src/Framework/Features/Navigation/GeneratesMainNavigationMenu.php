<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;

use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Refactor to move logic to the new action
 */
class GeneratesMainNavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    protected function __construct()
    {
        $this->items = new Collection();
    }

    public static function handle(): NavigationMenu
    {
        $menu = new static();

        $menu->generate();
        $menu->parent__sortByPriority();
        $menu->parent__removeDuplicateItems();

        return new NavigationMenu($menu->items);
    }

    protected function generate(): void
    {
        $this->parent__generate();

        if ($this->dropdownsEnabled()) {
            $this->moveGroupedItemsIntoDropdowns();
        }
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
        return $this->parent__canAddRoute($route) && (! $route->getPage() instanceof DocumentationPage || $route->is(DocumentationPage::homeRouteName()));
    }

    protected function canAddItemToDropdown(NavItem $item): bool
    {
        return $item->getGroup() !== null;
    }

    protected function dropdownsEnabled(): bool
    {
        return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }

    protected function parent__generate(): void
    {
        Routes::each(function (Route $route): void {
            if ($this->canAddRoute($route)) {
                $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
            }
        });

        collect(Config::getArray('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });
    }

    protected function parent__canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation();
    }

    protected function parent__removeDuplicateItems(): void
    {
        $this->items = $this->items->unique(function (NavItem $item): string {
            // Filter using a combination of the group and label to allow duplicate labels in different groups
            return $item->getGroup().$item->label;
        });
    }

    protected function parent__sortByPriority(): void
    {
        $this->items = $this->items->sortBy('priority')->values();
    }
}
