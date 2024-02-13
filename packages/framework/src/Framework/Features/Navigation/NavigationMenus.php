<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * Interact with the navigation menus for the project.
 */
class NavigationMenus
{
    /**
     * The menus that are available for the project, keyed by their name identifier.
     *
     * @var array<string, \Hyde\Framework\Features\Navigation\BaseNavigationMenu>
     */
    protected array $menus = [];
}
