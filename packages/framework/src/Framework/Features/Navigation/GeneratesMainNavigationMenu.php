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
    public static function handle(): NavigationMenu
    {
        $menu = new static(NavigationMenu::class);

        $menu->generate();

        return new NavigationMenu($menu->items);
    }
}
