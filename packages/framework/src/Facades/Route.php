<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use function str_replace;

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
        return Routes::get(str_replace('.', '/', $routeKey));
    }

    /**
     * Get a route from the route index for the specified route key or throw an exception.
     *
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getOrFail(string $routeKey): \Hyde\Support\Models\Route
    {
        return static::get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /**
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\Kernel\RouteCollection<\Hyde\Support\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return Hyde::routes();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): ?\Hyde\Support\Models\Route
    {
        return Hyde::currentRoute();
    }

    /**
     * Determine if the supplied route key exists in the route index.
     */
    public static function exists(string $routeKey): bool
    {
        return Routes::has($routeKey);
    }
}
