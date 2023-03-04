<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\RouteCollection;
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
        return static::getFacadeRoot()->getRoute($routeKey);
    }

    public static function getRoutes(?string $pageClass = null): RouteCollection
    {
        return static::getFacadeRoot()->getRoutes($pageClass);
    }
}
