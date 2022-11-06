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
     */
    public static function get(string $routeKey): ?\Hyde\Support\Models\Route
    {
        return \Hyde\Support\Models\Route::get($routeKey);
    }

    /**
     * Get a route from the route index for the specified route key or throw an exception.
     *
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getOrFail(string $routeKey): \Hyde\Support\Models\Route
    {
        return \Hyde\Support\Models\Route::getOrFail($routeKey);
    }

    /**
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\RouteCollection<\Hyde\Support\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return \Hyde\Support\Models\Route::all();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): ?\Hyde\Support\Models\Route
    {
        return \Hyde\Support\Models\Route::current();
    }

    /**
     * Determine if the supplied route key exists in the route index.
     */
    public static function exists(string $routeKey): bool
    {
        return \Hyde\Support\Models\Route::exists($routeKey);
    }
}
