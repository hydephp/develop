<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Refactor to move logic to the new action
 */
class GeneratesMainNavigationMenu
{
    public static function handle(): NavigationMenu
    {
        $navigation = \Hyde\Framework\Features\Navigation\MainNavigationMenu::temp__generate();

        return new NavigationMenu($navigation->items);
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
