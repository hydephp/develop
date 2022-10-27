<?php

declare(strict_types=1);

namespace Hyde\Routing;

use Hyde\Foundation\RouteCollection;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use function str_replace;

class Router
{
    /**
     * Get a route from the route index for the specified route key.
     *
     * Alias for static::getFromKey().
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Models\Support\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function get(string $routeKey): Route
    {
        return static::getFromKey($routeKey);
    }

    /**
     * Get a route from the route index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo, posts.foo
     * @return \Hyde\Framework\Models\Support\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromKey(string $routeKey): Route
    {
        return Hyde::routes()->get(str_replace('.', '/', $routeKey))
            ?? throw new RouteNotFoundException($routeKey);
    }

    /**
     * Get a route from the route index for the specified source file path.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Models\Support\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromSource(string $sourceFilePath): Route
    {
        return Hyde::routes()->first(function (Route $route) use ($sourceFilePath) {
            return $route->getSourcePath() === $sourceFilePath;
        }) ?? throw new RouteNotFoundException($sourceFilePath);
    }

    /**
     * Get a route from the route index for the supplied page model.
     *
     * @param  \Hyde\Framework\Concerns\HydePage  $page
     * @return \Hyde\Framework\Models\Support\Route
     */
    public static function getFromModel(HydePage $page): Route
    {
        return $page->getRoute();
    }

    /**
     * Get all routes from the route index.
     *
     * @return \Hyde\Foundation\RouteCollection<\Hyde\Framework\Models\Support\Route>
     */
    public static function all(): RouteCollection
    {
        return Hyde::routes();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): Route
    {
        return Hyde::currentRoute() ?? throw new RouteNotFoundException('current');
    }

    /**
     * Get the home route, usually the index page route.
     */
    public static function home(): Route
    {
        return static::getFromKey('index');
    }

    /**
     * Determine if the supplied route key exists in the route index.
     *
     * @param  string  $routeKey
     * @return bool
     */
    public static function exists(string $routeKey): bool
    {
        return Hyde::routes()->has($routeKey);
    }
}
