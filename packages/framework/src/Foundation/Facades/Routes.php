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
    public static function getFacadeRoot(): RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }

    public static function getRoute(string $routeKey): Route
    {
        return static::getFacadeRoot()->items[$routeKey] ?? throw new RouteNotFoundException($routeKey . ' in route collection');
    }

    public static function getRoutes(?string $pageClass = null): RouteCollection
    {
        return ! $pageClass ? static::getFacadeRoot() : static::getFacadeRoot()->filter(function (Route $route) use ($pageClass): bool {
            return $route->getPage() instanceof $pageClass;
        });
    }
}
