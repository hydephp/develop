<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\RouteCollection;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 */
class Route
{
    /**
     * Get a route from the route index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Routing\Route|null
     */
    public static function get(string $routeKey): ?\Hyde\Routing\Route
    {
        return \Hyde\Routing\Route::get($routeKey);
    }

    /**
     * Get a route from the route index for the specified route key or throw an exception.
     *
     * @param  string  $routeKey
     * @return \Hyde\Routing\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getOrFail(string $routeKey): \Hyde\Routing\Route
    {
        return \Hyde\Routing\Route::getOrFail($routeKey);
    }

    /**
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\RouteCollection<\Hyde\Routing\Route>
     */
    public static function all(): RouteCollection
    {
        return \Hyde\Routing\Route::all();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): ?\Hyde\Routing\Route
    {
        return \Hyde\Routing\Route::current();
    }

    /**
     * Get the home route, usually the index page route.
     */
    public static function home(): ?\Hyde\Routing\Route
    {
        return \Hyde\Routing\Route::home();
    }

    /**
     * Determine if the supplied route key exists in the route index.
     *
     * @param  string  $routeKey
     * @return bool
     */
    public static function exists(string $routeKey): bool
    {
        return \Hyde\Routing\Route::exists($routeKey);
    }
}
