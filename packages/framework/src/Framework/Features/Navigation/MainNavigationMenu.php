<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use function app;

class MainNavigationMenu extends NavigationMenu
{
    public static function get(): static
    {
        /** @var self::class $menu */
        $menu = app('navigation.main');

        return $menu;
    }
}
