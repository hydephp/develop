<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\RouteCollection;
use Hyde\Routing\Route as RouteModel;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 */
class Route
{
    public static function get(string $routeKey): ?RouteModel
    {
        return RouteModel::get($routeKey);
    }

    public static function getOrFail(string $routeKey): RouteModel
    {
        return RouteModel::getOrFail($routeKey);
    }

    public static function all(): RouteCollection
    {
        return RouteModel::all();
    }

    public static function current(): ?RouteModel
    {
        return RouteModel::current();
    }

    public static function home(): ?RouteModel
    {
        return RouteModel::home();
    }

    public static function exists(string $routeKey): bool
    {
        return RouteModel::exists($routeKey);
    }
}
