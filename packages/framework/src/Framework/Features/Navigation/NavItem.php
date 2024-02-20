<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use Stringable;
use Hyde\Support\Models\ExternalRoute;

use function is_string;
use function strtolower;

/**
 * Abstraction for a navigation menu item. Used by the MainNavigationMenu and DocumentationSidebar classes.
 *
 * @todo Consider splitting facade methods to actual facade class.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::forLink() for an external or un-routed link
 */
class NavItem implements Stringable
{
    protected Route $destination;
    protected string $label;
    protected int $priority;
    protected ?string $group;

    /** The "slugified" version of the label. */
    protected string $identifier;

    /** @var array<\Hyde\Framework\Features\Navigation\NavItem> */
    protected array $children;

    /**
     * Create a new navigation menu item.
     */
    public function __construct(Route|string $destination, string $label, int $priority = 500, ?string $group = null, array $children = [])
    {
        if (is_string($destination)) {
            $destination = Routes::get($destination) ?? new ExternalRoute($destination);
        }

        $this->destination = $destination;
        $this->label = $label;
        $this->priority = $priority;
        $this->group = static::normalizeGroupKey($group);
        $this->identifier = $this->makeIdentifier($label);
        $this->children = $children;
    }

    /**
     * Create a new navigation menu item from a route.
     */
    public static function fromRoute(Route $route, ?string $label = null, ?int $priority = null, ?string $group = null): static
    {
        return new static(
            $route,
            $label ?? $route->getPage()->navigationMenuLabel(),
            $priority ?? $route->getPage()->navigationMenuPriority(),
            $group ?? $route->getPage()->data('navigation.group'),
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     */
    public static function forLink(string $href, string $label, int $priority = 500): static
    {
        return new static($href, $label, $priority);
    }

    /**
     * Create a new navigation menu item leading to a Route model.
     *
     * @param  \Hyde\Support\Models\Route|string<\Hyde\Support\Models\RouteKey>  $route  Route model or route key
     * @param  int|null  $priority  Leave blank to use the priority of the route's corresponding page.
     * @param  string|null  $label  Leave blank to use the label of the route's corresponding page.
     * @param  string|null  $group  Leave blank to use the group of the route's corresponding page.
     */
    public static function forRoute(Route|string $route, ?string $label = null, ?int $priority = null, ?string $group = null): static
    {
        return static::fromRoute($route instanceof Route ? $route : Routes::getOrFail($route), $label, $priority, $group);
    }

    /**
     * Create a new dropdown navigation menu item.
     *
     * @TODO: Might be more semantic to have this named something else, as it also includes sidebars groups.
     *
     * @param  string  $label  The label of the dropdown item.
     * @param  array<NavItem>  $items  The items to be included in the dropdown.
     * @param  int|null  $priority  The priority of the dropdown item. Leave blank to use the default priority.
     */
    public static function dropdown(string $label, array $items, ?int $priority = null): static
    {
        return new static('', static::normalizeGroupLabel($label), $priority ?? static::searchForDropdownPriorityInNavigationConfig($label) ?? 999, $label, $items);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->getLink();
    }

    /**
     * Get the destination route of the navigation item.
     */
    public function getDestination(): Route
    {
        return $this->destination;
    }

    /**
     * Resolve the destination link of the navigation item.
     */
    public function getLink(): string
    {
        return (string) $this->destination;
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
     * Get the group identifier of the navigation item, if any.
     *
     * For sidebars this is the category key, for navigation menus this is the dropdown key.
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Get the identifier of the navigation item.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the children of the navigation item.
     *
     * For the main navigation menu, this stores any dropdown items.
     *
     * @return array<\Hyde\Framework\Features\Navigation\NavItem>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Check if the NavItem instance has children.
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * Check if the NavItem instance is the current page.
     */
    public function isCurrent(): bool
    {
        return Hyde::currentRoute()->getLink() === (string) $this->destination;
    }

    /**
     * Add a navigation item to the children of the navigation item.
     */
    public function addChild(NavItem $item): void
    {
        // Todo: Ensure that the item has a group identifier by creating it from the label if it doesn't exist?

        $this->children[] = $item;
    }

    protected static function normalizeGroupKey(?string $group): ?string
    {
        return $group ? Str::slug($group) : null;
    }

    protected static function makeIdentifier(string $label): string
    {
        return Str::slug($label); // Todo: If it's a dropdown based on a subdirectory, we should use the subdirectory as the identifier
    }

    // TODO: Consider moving all of these to a dropdown factory
    protected static function searchForDropdownPriorityInNavigationConfig(string $groupKey): ?int
    {
        return Config::getArray('hyde.navigation.order', [])[$groupKey] ?? null;
    }

    protected static function normalizeGroupLabel(string $label): string
    {
        // If there is no label, and the group is a slug, we can make a title from it
        if ($label === strtolower($label)) {
            return Hyde::makeTitle($label);
        }

        return $label;
    }
}
