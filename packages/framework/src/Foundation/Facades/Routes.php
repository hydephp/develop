<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Support\Models\Route;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\RouteCollection
 */
class Routes extends Facade
{
    public static function getRoute(string $routeKey): Route
    {
        return static::getFacadeRoot()->get($routeKey) ?? throw new RouteNotFoundException(message: "Route [$routeKey] not found in route collection");
    }

    public static function getRoutes(?string $pageClass = null): RouteCollection
    {
        return $pageClass ? static::getFacadeRoot()->filter(function (Route $route) use ($pageClass): bool {
            return $route->getPage() instanceof $pageClass;
        }) : static::getFacadeRoot();
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    public static function getFacadeRoot(): RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }
}
