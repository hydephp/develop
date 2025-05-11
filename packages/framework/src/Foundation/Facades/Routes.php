<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

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

    public static function exists(string $routeKey): bool
    {
        return static::getFacadeRoot()->has($routeKey);
    }

    public static function find(string $routeKey): ?Route
    {
        return static::getFacadeRoot()->get($routeKey);
    }

    /** @throws \Hyde\Framework\Exceptions\RouteNotFoundException */
    public static function getOrFail(string $routeKey): Route
    {
        return static::getFacadeRoot()->getRoute($routeKey);
    }

    public static function all(): RouteCollection
    {
        return static::getFacadeRoot()->getRoutes();
    }

    /** Get the current route for the page being rendered. */
    public static function current(): ?Route
    {
        return Hyde::currentRoute();
    }
}
