<?php

namespace Hyde\Framework\Modules\Navigation;

/**
 * Defines the requirements for a model to be displayed in the navigation menu.
 *
 * @method string getRouteKey() The getRouteKey method should be implemented by the model.
 */
interface NavigationMenuItemContract
{
    /**
     * Should the item should be displayed in the navigation menu?
     *
     * @return bool
     */
    public function showInNavigation(): bool;

    /**
     * The relative priority, determining the position of the item in the menu.
     *
     * @return int
     */
    public function navigationMenuPriority(): int;

    /**
     * The page title to display in the navigation menu.
     *
     * @return string
     */
    public function navigationMenuLabel(): string;

    /**
     * Not yet implemented.
     *
     * If an item returns a route collection,
     * it will automatically be made into a dropdown.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Modules\Routing\Route>
     */
    // public function navigationMenuChildren(): Collection;
}
