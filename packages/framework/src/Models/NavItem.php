<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Modules\Routing\RouteContract;

/**
 * Abstraction for a navigation menu item.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly
 *   2. (NYI) Or, pass a source file path, which will be resolved into a Route
 *   3. (NYI) Or supply a fully qualified URI starting with HTTP(S)
 *      and the item will lead directly to that link.
 */
class NavItem
{
    public RouteContract $route;
    public string $href;

    public string $title;
    public int $priority;
    public bool $hidden;

    /**
     * @param  \Hyde\Framework\Modules\Routing\RouteContract  $route
     * @param  string  $title
     * @param  int  $priority
     * @param  bool  $hidden
     */
    public function __construct(RouteContract $route, string $title, int $priority = 500, bool $hidden = false)
    {
        $this->route = $route;
        $this->title = $title;
        $this->priority = $priority;
        $this->hidden = $hidden;
    }

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
     * Resolve a link to the navigation item.
     *
     * @param  string  $currentPage
     * @return string
     */
    public function resolveLink(string $currentPage = ''): string
    {
        return $this->route->getLink($currentPage);
    }

    public function __toString(): string
    {
        return $this->resolveLink();
    }
}
