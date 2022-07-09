<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Modules\Routing\RouteContract;

/**
 * Abstraction for a navigation menu item.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::leadsTo(URI, Title, ?priority) for an external or unrouted link
 */
class NavItem
{
    public RouteContract $route;
    public string $href;

    public string $title;
    public int $priority;
    public bool $hidden;

    /**
     * Create a new navigation menu item.
     *
     * @param \Hyde\Framework\Modules\Routing\RouteContract|null $route
     * @param string $title
     * @param int $priority
     * @param bool $hidden
     */
    public function __construct(?RouteContract $route, string $title, int $priority = 500, bool $hidden = false)
    {
        if ($route !== null) {
            $this->route = $route;
        }

        $this->title = $title;
        $this->priority = $priority;
        $this->hidden = $hidden;
    }

    /**
     * Create a new navigation menu item from a route.
     */
    public static function fromRoute(RouteContract $route): static
    {
        return new static(
            $route,
            $route->getSourceModel()->navigationMenuTitle(),
            $route->getSourceModel()->navigationMenuPriority(),
            ! $route->getSourceModel()->showInNavigation()
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     */
    public static function leadsTo(string $href, string $title, int $priority = 500): static
    {
        return (new static(null, $title, $priority, false))->setDestination($href);
    }

    /**
     * Resolve a link to the navigation item.
     *
     * @param  string  $currentPage
     * @return string
     */
    public function resolveLink(string $currentPage = ''): string
    {
        return $this->href ?? $this->route->getLink($currentPage);
    }

    public function __toString(): string
    {
        return $this->resolveLink();
    }

    /**
     * While you can always add the priority to the constructor,
     * this fluent method is provided for convenience.
     */
    public function withPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    protected function setDestination(string $href): self
    {
        $this->href = $href;
        return $this;
    }
}
