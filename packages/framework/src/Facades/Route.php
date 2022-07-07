<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Modules\Routing\Route as RouteModel;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\RouteFacadeContract;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Modules\Routing\Route
 * @see \Hyde\Framework\Testing\Feature\RouteFacadeTest
 */
class Route implements RouteFacadeContract
{
    /** @inheritDoc */
    public static function get(string $routeKey): ?RouteModel
    {
        return RouteModel::get($routeKey);
    }

    /** @inheritDoc */
    public static function getFromKey(string $routeKey): ?RouteModel
    {
        return RouteModel::getFromKey($routeKey);
    }

    /** @inheritDoc */
    public static function getFromSource(string $sourceFilePath): ?RouteModel
    {
        return RouteModel::getFromSource($sourceFilePath);
    }

    /** @inheritDoc */
    public static function getFromModel(PageContract $page): ?RouteModel
    {
        return RouteModel::getFromModel($page);
    }

    /** @inheritDoc */
    public static function all(): Collection
    {
        return RouteModel::all();
    }
}
