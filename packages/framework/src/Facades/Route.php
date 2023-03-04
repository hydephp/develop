<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Support\Models\RouteKey;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 */
class Route
{
    /**
     * Get a route from the route index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
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
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\Kernel\RouteCollection<\Hyde\Support\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return Routes::getRoutes();
    }

    /**
     * Determine if the supplied route key exists in the route index.
     */
    public static function exists(string $routeKey): bool
    {
        return Routes::has($routeKey);
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): ?\Hyde\Support\Models\Route
    {
        return Hyde::currentRoute();
    }
}
