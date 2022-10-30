<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use Stringable;

/**
 * Abstraction for a navigation menu item. Used by the NavigationMenu and DocumentationSidebar classes.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::toLink() for an external or un-routed link
 */
class NavItem implements Stringable
{
    public Route $route;
    public string $href;

    public string $label;
    public int $priority;
    public bool $hidden;

    /**
     * Create a new navigation menu item.
     *
     * @param  \Hyde\Support\Models\Route|null  $route
     * @param  string  $label
     * @param  int  $priority
     * @param  bool  $hidden
     */
    public function __construct(?Route $route, string $label, int $priority = 500, bool $hidden = false)
    {
        if ($route !== null) {
            $this->route = $route;
        }

        $this->label = $label;
        $this->priority = $priority;
        $this->hidden = $hidden;
    }

    /**
     * Create a new navigation menu item from a route.
     */
    public static function fromRoute(Route $route): static
    {
        return new self(
            $route,
            $route->getPage()->navigationMenuLabel(),
            $route->getPage()->navigationMenuPriority(),
            ! $route->getPage()->showInNavigation()
        );
    }

    /**
     * Create a new navigation menu item leading to a external URI.
     */
    public static function toLink(string $href, string $label, int $priority = 500): static
    {
        return (new self(null, $label, $priority, false))->setDestination($href);
    }

    /**
     * Create a new navigation menu item leading to a Route model.
     */
    public static function toRoute(Route $route, string $label, int $priority = 500): static
    {
        return new self($route, $label, $priority, false);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function resolveLink(): string
    {
        return $this->href ?? $this->route->getLink();
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->resolveLink();
    }

    /**
     * Check if the NavItem instance is the current page.
     */
    public function isCurrent(?HydePage $current = null): bool
    {
        if ($current === null) {
            $current = Hyde::currentRoute()->getPage();
        }

        if (! isset($this->route)) {
            return ($current->getRoute()->getRouteKey() === $this->href)
            || ($current->getRoute()->getRouteKey().'.html' === $this->href);
        }

        return $current->getRoute()->getRouteKey() === $this->route->getRouteKey();
    }

    protected function setDestination(string $href): static
    {
        $this->href = $href;

        return $this;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->normalizeGroupKey($this->route->getPage()->get('navigation.group'));
    }

    protected function normalizeGroupKey(?string $group): ?string
    {
        return empty($group) ? null : Str::slug($group);
    }
}
