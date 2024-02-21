<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;

use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu
 */
class GeneratesMainNavigationMenu extends BaseMenuGenerator
{
    protected function generate(): void
    {
        parent::generate();

        collect(Config::getArray('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });
    }

    protected function canGroupRoute(Route $route): bool
    {
        return parent::canGroupRoute($route) && $route->getPage()->navigationMenuGroup() !== null;
    }
}
