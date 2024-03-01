<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use Stringable;
use Hyde\Support\Models\ExternalRoute;

use function min;
use function collect;
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
    protected ?Route $destination;
    protected string $label;
    protected int $priority;
    protected ?string $group;

    /** The "slugified" version of the label used to uniquely identify the item for things like active state comparisons. */
    protected string $identifier;

    /** @var array<\Hyde\Framework\Features\Navigation\NavItem> */
    protected array $children = [];

    /**
     * Create a new navigation menu item.
     *
     * @param  array<\Hyde\Framework\Features\Navigation\NavItem>  $children
     */
    public function __construct(Route|string|null $destination, string $label, int $priority = NavigationMenu::DEFAULT, ?string $group = null, array $children = [])
    {
        if (is_string($destination)) {
            $destination = Routes::get($destination) ?? new ExternalRoute($destination);
        }

        $this->destination = $destination;
        $this->label = $label;
        $this->priority = $priority;
        $this->group = static::normalizeGroupKey($group);
        $this->identifier = static::makeIdentifier($label);
        $this->addChildren($children);
    }

    /**
     * Create a new navigation menu item leading to a Route instance.
     *
     * @param  \Hyde\Support\Models\Route|string<\Hyde\Support\Models\RouteKey>  $route  Route instance or route key
     * @param  int|null  $priority  Leave blank to use the priority of the route's corresponding page.
     * @param  string|null  $label  Leave blank to use the label of the route's corresponding page.
     * @param  string|null  $group  Leave blank to use the group of the route's corresponding page.
     */
    public static function forRoute(Route|string $route, ?string $label = null, ?int $priority = null, ?string $group = null): static
    {
        $route = $route instanceof Route ? $route : Routes::getOrFail($route);

        return new static(
            $route,
            $label ?? $route->getPage()->navigationMenuLabel(),
            $priority ?? $route->getPage()->navigationMenuPriority(),
            $group ?? $route->getPage()->navigationMenuGroup(),
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     */
    public static function forLink(string $href, string $label, int $priority = NavigationMenu::DEFAULT): static
    {
        return new static($href, $label, $priority);
    }

    /**
     * Create a new dropdown navigation menu item.
     *
     * @param  string  $label  The label of the dropdown item.
     * @param  array<NavItem>  $items  The items to be included in the dropdown.
     * @param  int  $priority  The priority of the dropdown item. Leave blank to use the default priority, which is last in the menu.
     */
    public static function forGroup(string $label, array $items, int $priority = NavigationMenu::LAST): static
    {
        return new static(null, $label, $priority, $label, $items);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->getLink();
    }

    /**
     * Get the destination route of the navigation item. For dropdowns, this will return null.
     */
    public function getDestination(): ?Route
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
     *
     * For sidebar groups, this is the priority of the lowest priority child, unless the dropdown has a lower priority.
     */
    public function getPriority(): int
    {
        if ($this->hasChildren() && $this->children[0]->getDestination()->getPageClass() === DocumentationPage::class) {
            return min($this->priority, collect($this->getChildren())->min(fn (NavItem $child): int => $child->getPriority()));
        }

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
     * Check if the NavItem instance is the current page being rendered.
     */
    public function isCurrent(): bool
    {
        return Hyde::currentRoute()->getLink() === $this->destination->getLink();
    }

    /**
     * Add a navigation item to the children of the navigation item.
     *
     * This will turn the parent item into a dropdown. Its destination will be set to null.
     */
    public function addChild(NavItem $item): static
    {
        $item->group ??= $this->group;

        $this->children[] = $item;
        $this->destination = null;

        return $this;
    }

    /**
     * Add multiple navigation items to the children of the navigation item.
     *
     * @param  array<\Hyde\Framework\Features\Navigation\NavItem>  $items
     */
    public function addChildren(array $items): static
    {
        foreach ($items as $item) {
            $this->addChild($item);
        }

        return $this;
    }

    protected static function normalizeGroupKey(?string $group): ?string
    {
        return $group ? Str::slug($group) : null;
    }

    protected static function makeIdentifier(string $label): string
    {
        return Str::slug($label);
    }
}
