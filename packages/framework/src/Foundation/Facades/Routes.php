<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\RouteCollection
 */
class Routes extends Facade
{
    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    public static function getFacadeRoot(): RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }

    public static function exists(string $routeKey): bool
    {
        return HydeKernel::getInstance()->routes()->has(RouteKey::normalize($routeKey));
    }

    public static function get(string $routeKey): ?Route
    {
        return HydeKernel::getInstance()->routes()->get(RouteKey::normalize($routeKey));
    }

    /** @throws \Hyde\Framework\Exceptions\RouteNotFoundException */
    public static function getOrFail(string $routeKey): Route
    {
        return HydeKernel::getInstance()->routes()->getRoute(RouteKey::normalize($routeKey));
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<\Hyde\Support\Models\Route> */
    public static function all(): RouteCollection
    {
        return HydeKernel::getInstance()->routes()->getRoutes();
    }

    /** Get the current route for the page being rendered. */
    public static function current(): ?Route
    {
        return Hyde::currentRoute();
    }
}
