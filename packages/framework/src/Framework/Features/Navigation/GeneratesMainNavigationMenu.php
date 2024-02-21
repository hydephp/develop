<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 */
class GeneratesMainNavigationMenu
{
    public static function handle(): NavigationMenu
    {
        return NavigationMenuGenerator::handle(NavigationMenu::class);
    }
}
