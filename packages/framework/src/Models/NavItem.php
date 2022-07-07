<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Facades\Route;
use Hyde\Framework\Modules\Routing\RouteContract;

/**
 * Abstraction for a navigation menu item.
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly
 *   2. Or, pass a source file path, which will be resolved into a Route
 *   3. Or supply a fully qualified URI starting with HTTP(S)
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
     * @param string|\Hyde\Framework\Modules\Routing\RouteContract $destination
     * @param string $title
     * @param int $priority
     * @param bool $hidden
     */
    public function __construct(string|RouteContract $destination, string $title, int $priority, bool $hidden)
    {
        $this->leadsTo($destination);

        $this->title = $title;
        $this->priority = $priority;
        $this->hidden = $hidden;
    }

    /**
     * Static alias for __construct().
     *
     * @param ...$params
     * @return static
     */
    public static function make(...$params): static
    {
        return new static(...$params);
    }

    /**
     * @param string|\Hyde\Framework\Modules\Routing\RouteContract $destination
     * @return $this
     */
    public function leadsTo(string|RouteContract $destination): self
    {
        if ($destination instanceof RouteContract) {
            $this->route = $destination;
        }

        if (str_starts_with($destination, 'http')) {
            $this->href = $destination;
        }

        $this->route = Route::get($destination);

        return $this;
    }

    /**
     * Resolve a link to the navigation item.
     *
     * @param string $currentPage
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
}
