<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * Manages the navigation menus for the project.
 */
class NavigationManager
{
    /**
     * The menus that are available for the project, keyed by their name identifier.
     *
     * @var array<string, \Hyde\Framework\Features\Navigation\NavigationMenu>
     */
    protected array $menus = [];

    /**
     * Register a new menu for the project.
     */
    public function registerMenu(string $name, NavigationMenu $menu): void
    {
        $this->menus[$name] = $menu;
    }

    /**
     * Get a menu by its name.
     */
    public function getMenu(string $name): NavigationMenu
    {
        return $this->menus[$name];
    }
}
