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
 * @todo Refactor to reduce code overlapping with the Route class
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::toLink() for an external or un-routed link
 */
class NavItem implements Stringable
{
    /** @deprecated Use $destination instead */
    public Route $route;

    /** @deprecated Use $destination instead */
    public string $href;

    public string $destination;

    public string $label;
    public int $priority;
    public bool $hidden;

    /**
     * Create a new navigation menu item.
     */
    public function __construct(Route|string $destination, string $label, int $priority = 500, bool $hidden = false)
    {
        $this->destination = $destination instanceof Route ? $destination->getLink() : $destination;

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
            $route->getLink(),
            $route->getPage()->navigationMenuLabel(),
            $route->getPage()->navigationMenuPriority(),
            ! $route->getPage()->showInNavigation()
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     */
    public static function toLink(string $href, string $label, int $priority = 500): static
    {
        return (new self($href, $label, $priority, false))->setDestination($href);
    }

    /**
     * Create a new navigation menu item leading to a Route model.
     */
    public static function toRoute(Route $route, string $label, int $priority = 500): static
    {
        return new self($route->getLink(), $label, $priority, false);
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function resolveLink(): string
    {
        return $this->destination;
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

    /** @deprecated Made obsolete by $destination */
    protected function setDestination(string $href): static
    {
        $this->href = $href;

        return $this;
    }

    /** @deprecated This helper is only used in tests and could be removed to simplify the class */
    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->normalizeGroupKey($this->getRoute()?->getPage()->data('navigation.group'));
    }

    public function getRoute(): ?Route
    {
        return $this->route ?? null;
    }

    protected function normalizeGroupKey(?string $group): ?string
    {
        return empty($group) ? null : Str::slug($group);
    }
}
