<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Support\Models\RouteKey;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 *
 * To access a route you need the route key which is the equivalent of the URL path of the compiled page.
 */
class Route
{
    /**
     * Get a route from the route index for the specified route key.
     */
    public static function get(string $routeKey): ?\Hyde\Support\Models\Route
    {
        return Routes::get(RouteKey::normalize($routeKey));
    }

    /**
     * Get a route from the route index for the specified route key or throw an exception.
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getOrFail(string $routeKey): \Hyde\Support\Models\Route
    {
        return Routes::getRoute(RouteKey::normalize($routeKey));
    }

    /**
     * Determine if the supplied route key exists in the route index.
     */
    public static function exists(string $routeKey): bool
    {
        return Routes::has(RouteKey::normalize($routeKey));
    }

    /**
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\Kernel\RouteCollection<\Hyde\Support\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return Routes::getRoutes();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): ?\Hyde\Support\Models\Route
    {
        return Hyde::currentRoute();
    }
}
