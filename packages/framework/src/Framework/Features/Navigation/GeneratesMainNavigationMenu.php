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
    public function execute(): NavigationMenu
    {
        $navigation = \Hyde\Framework\Features\Navigation\MainNavigationMenu::create();

        return new NavigationMenu($navigation->items);
    }
}
