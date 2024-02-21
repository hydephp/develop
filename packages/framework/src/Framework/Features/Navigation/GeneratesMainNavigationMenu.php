<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu
 */
class GeneratesMainNavigationMenu extends BaseMenuGenerator
{
    public static function handle(string $menuType = NavigationMenu::class): NavigationMenu
    {
        return BaseMenuGenerator::handle(NavigationMenu::class);
    }
}
