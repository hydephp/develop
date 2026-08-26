<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Facades\Localization;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Facades\Facade;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 *
 * To access a route you need the route key which is the equivalent of the URL path of the compiled page.
 *
 * @mixin \Hyde\Foundation\Kernel\RouteCollection
 */
class Routes extends Facade
{
    public static function getFacadeRoot(): RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }

    /**
     * Check if a route exists by its route key.
     */
    public static function exists(string $routeKey): bool
    {
        return static::getFacadeRoot()->findRoute($routeKey) !== null;
    }

    /**
     * Try to get a route by its route key. If it doesn't exist, null is returned.
     */
    public static function find(string $routeKey): ?Route
    {
        return static::getFacadeRoot()->findRoute($routeKey);
    }

    /**
     * Get a route by its route key. If it doesn't exist, an exception is thrown.
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function get(string $routeKey): Route
    {
        return static::getFacadeRoot()->getRoute($routeKey);
    }

    /**
     * Get all the routes for the site as a collection of route instances, keyed by route key.
     *
     * @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return static::getFacadeRoot()->getRoutes();
    }

    /**
     * Get the route with exactly the given route key, or null if there is none.
     *
     * Unlike find(), the key is not resolved within the language in effect, so this is what
     * to use where the key is already a concrete path, such as an incoming request URL,
     * as opposed to a logical reference to a page, which is what find() resolves.
     */
    public static function findExact(string $routeKey): ?Route
    {
        return static::getFacadeRoot()->get($routeKey);
    }

    /**
     * Get all the routes belonging to the given language, keyed by route key.
     *
     * Defaults to the language of the page currently being rendered, which for a site
     * that is not localized is null, matching every route.
     *
     * @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route>
     */
    public static function forLanguage(?string $language = null): RouteCollection
    {
        return static::getFacadeRoot()->getRoutesForLanguage($language ?? Localization::currentLanguage());
    }

    /**
     * Get the route instance for the page currently being rendered.
     * If a render is not in progress, this will return null.
     */
    public static function current(): ?Route
    {
        return Hyde::currentRoute();
    }
}
