<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use Stringable;

use function is_string;

/**
 * Abstraction for a navigation menu item. Used by the MainNavigationMenu and DocumentationSidebar classes.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavigationItem::fromRoute() to use data from the route
 *   3. You can use NavigationItem::create() for an external or un-routed link
 */
class NavigationItem implements NavigationElement, Stringable
{
    protected NavigationDestination $destination;
    protected string $label;
    protected int $priority;

    // TODO: Do we actually need this? We should just care if it's physically stored in a group.
    protected ?string $group = null;

    /**
     * Create a new navigation menu item with your own properties.
     *
     * @param  \Hyde\Support\Models\Route|string  $destination  Route instance, route key, or external URI.
     * @param  string  $label  The label of the navigation item.
     * @param  int  $priority  The priority to determine the order of the navigation item.
     * @param  string|null  $group  The dropdown/group key of the navigation item, if any.
     */
    public function __construct(Route|string $destination, string $label, int $priority = NavigationMenu::DEFAULT, ?string $group = null)
    {
        $this->destination = new NavigationDestination($destination);

        $this->label = $label;
        $this->priority = $priority;

        if ($group !== null) {
            $this->group = static::normalizeGroupKey($group);
        }
    }

    /**
     * Create a new navigation menu item, automatically filling in the properties from a Route instance if provided.
     *
     * @param  \Hyde\Support\Models\Route|string<\Hyde\Support\Models\RouteKey>|string  $destination  Route instance or route key, or external URI.
     * @param  int|null  $priority  Leave blank to use the priority of the route's corresponding page, if there is one tied to the route.
     * @param  string|null  $label  Leave blank to use the label of the route's corresponding page, if there is one tied to the route.
     * @param  string|null  $group  Leave blank to use the group of the route's corresponding page, if there is one tied to the route.
     */
    public static function create(Route|string $destination, ?string $label = null, ?int $priority = null, ?string $group = null): self
    {
        if (is_string($destination) && Routes::has($destination)) {
            $destination = Routes::get($destination);
        }

        if ($destination instanceof Route) {
            return new self(
                $destination,
                $label ?? $destination->getPage()->navigationMenuLabel(),
                $priority ?? $destination->getPage()->navigationMenuPriority(),
                $group ?? $destination->getPage()->navigationMenuGroup(),
            );
        }

        return new self($destination, $label ?? '', $priority ?? NavigationMenu::DEFAULT, $group);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }

    /**
     * If the navigation item is a link to a routed page, get the corresponding page instance.
     */
    public function getPage(): ?HydePage
    {
        return $this->destination->getRoute()?->getPage();
    }

    /**
     * Get the destination route of the navigation item. For dropdowns, this will return null.
     *
     * @deprecated To simplify the class, we may remove this as we probably don't need it.
     */
    public function getRoute(): ?Route
    {
        return $this->destination->getRoute();
    }

    /**
     * Resolve the destination link of the navigation item.
     *
     * @deprecated May be renamed to getLink() in the future to better match its usage, and to match the Route class.
     */
    public function getUrl(): string
    {
        return $this->destination->getLink();
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
     *  For sidebars this is the category key, for navigation menus this is the dropdown key.
     *
     *  When using automatic subdirectory based groups, the subdirectory name is the group key.
     *  Otherwise, the group key is a 'slugified' version of the group's label.
     */
    public function getGroupKey(): ?string
    {
        return $this->group;
    }

    /**
     * Check if the NavigationItem instance is the current page being rendered.
     */
    public function isActive(): bool
    {
        return Hyde::currentRoute()->getLink() === $this->getUrl();
    }

    /** @return ($group is null ? null : string) */
    public static function normalizeGroupKey(?string $group): ?string
    {
        return $group ? Str::slug($group) : null;
    }
}
