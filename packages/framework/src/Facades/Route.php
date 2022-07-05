<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Routing\Route as RouteModel;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\RouteFacadeContract;

/**
 * @see \Hyde\Framework\Modules\Routing\Route
 * @see \Hyde\Framework\Testing\Feature\RouteFacadeTest
 */
class Route implements RouteFacadeContract
{
    /** @inheritDoc */
    public static function get(string $routeKey): ?RouteContract
    {
        return RouteModel::get($routeKey);
    }

    /** @inheritDoc */
    public static function getFromSource(string $sourceFilePath): ?RouteContract
    {
        return RouteModel::getFromSource($sourceFilePath);
    }
}
