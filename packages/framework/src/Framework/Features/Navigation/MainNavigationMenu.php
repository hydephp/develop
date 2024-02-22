<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use function app;

class MainNavigationMenu extends NavigationMenu
{
    /**
     * Get the navigation menu instance from the service container.
     */
    public static function get(): static
    {
        return app('navigation.main');
    }
}
