<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\RouteCollection;
use Hyde\Framework\Concerns\HydePage;
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

    public static function getFromKey(string $routeKey): ?RouteModel
    {
        return RouteModel::getFromKey($routeKey);
    }

    public static function getFromSource(string $sourceFilePath): ?RouteModel
    {
        return RouteModel::getFromSource($sourceFilePath);
    }

    public static function getFromModel(HydePage $page): ?RouteModel
    {
        return RouteModel::getFromModel($page);
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
