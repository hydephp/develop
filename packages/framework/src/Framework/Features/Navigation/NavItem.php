<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use Stringable;
use Hyde\Support\Models\ExternalRoute;

use function is_string;

/**
 * Abstraction for a navigation menu item. Used by the MainNavigationMenu and DocumentationSidebar classes.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::forLink() for an external or un-routed link
 *
 * Navigation items can be turned into dropdowns or sidebar groups by adding children.
 * Note that doing so will mean that any link on the parent will no longer be clickable,
 * as clicking the parent label will open the dropdown instead of leading to the destination.
 * For this reason, dropdown items will have their destination set to null.
 */
class NavItem implements Stringable
{
    protected ?Route $route;
    protected string $label;
    protected int $priority;
    protected ?string $group;

    /** @deprecated Use NavGroupItem::$items instead */
    protected array $items = [];

    /**
     * Create a new navigation menu item.
     *
     * @param  \Hyde\Support\Models\Route|string|null  $destination  Route instance, route key, or external URI. For dropdowns/groups, this should be null.
     * @param  string  $label  The label of the navigation item.
     * @param  int  $priority  The priority to determine the order of the navigation item.
     * @param  string|null  $group  The dropdown/group identifier of the navigation item, if any.
     */
    public function __construct(Route|string|null $destination, string $label, int $priority = NavigationMenu::DEFAULT, ?string $group = null)
    {
        if (is_string($destination)) {
            $destination = Routes::get($destination) ?? new ExternalRoute($destination);
        }

        $this->route = $destination;
        $this->label = $label;
        $this->priority = $priority;
        $this->group = static::normalizeGroupKey($group);
    }

    /**
     * Create a new navigation menu item leading to a Route instance.
     *
     * @param  \Hyde\Support\Models\Route|string<\Hyde\Support\Models\RouteKey>  $route  Route instance or route key
     * @param  int|null  $priority  Leave blank to use the priority of the route's corresponding page.
     * @param  string|null  $label  Leave blank to use the label of the route's corresponding page.
     * @param  string|null  $group  Leave blank to use the group of the route's corresponding page.
     */
    public static function forRoute(Route|string $route, ?string $label = null, ?int $priority = null, ?string $group = null): self
    {
        $route = $route instanceof Route ? $route : Routes::getOrFail($route);

        return new self(
            $route,
            $label ?? $route->getPage()->navigationMenuLabel(),
            $priority ?? $route->getPage()->navigationMenuPriority(),
            $group ?? $route->getPage()->navigationMenuGroup(),
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     */
    public static function forLink(string $href, string $label, int $priority = NavigationMenu::DEFAULT): self
    {
        return new self($href, $label, $priority);
    }

    /**
     * Create a new dropdown navigation menu item.
     *
     * @param  string  $label  The label of the dropdown item.
     * @param  array<NavItem>  $items  The items to be included in the dropdown.
     * @param  int  $priority  The priority of the dropdown item. Leave blank to use the default priority, which is last in the menu.
     */
    public static function forGroup(string $label, array $items, int $priority = NavigationMenu::LAST): NavGroupItem
    {
        return new NavGroupItem($label, $items, $priority);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }

    /**
     * Get the destination route of the navigation item. For dropdowns, this will return null.
     */
    public function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * Resolve the destination link of the navigation item.
     */
    public function getUrl(): string
    {
        return (string) $this->route;
    }

    /**
     * Get the label of the navigation item.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the priority to determine the order of the navigation item.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get the group identifier key of the navigation item, if any.
     *
     * When using automatic subdirectory based groups, the subdirectory name is the group key.
     * Otherwise, the group key is a "slugified" version of the group's label.
     *
     * For sidebars this is the category key, for navigation menus this is the dropdown key.
     */
    public function getGroupKey(): ?string
    {
        return $this->group;
    }

    /**
     * Check if the NavItem instance is the current page being rendered.
     */
    public function isActive(): bool
    {
        return Hyde::currentRoute()->getLink() === $this->route->getLink();
    }

    protected static function normalizeGroupKey(?string $group): ?string
    {
        return $group ? Str::slug($group) : null;
    }
}
