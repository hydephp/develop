<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Refactor to move logic to the new action
 */
class GeneratesMainNavigationMenu
{
    public static function handle(): NavigationMenu
    {
        $navigation = \Hyde\Framework\Features\Navigation\MainNavigationMenu::__generate();

        return new NavigationMenu($navigation->items);
    }
}
