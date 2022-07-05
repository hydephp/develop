<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Routing\Route as RouteModel;
use Hyde\Framework\Modules\Routing\RouteContract;

/**
 * @see \Hyde\Framework\Modules\Routing\Route
 * @see \Hyde\Framework\Testing\Feature\RouteFacadeTest
 */
class Route
{
    /**
     * Get a route from the Router index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function get(string $routeKey): ?RouteContract
    {
        return RouteModel::get($routeKey);
    }

    /**
     * Get a route from the Router index for the specified source file path.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function getFromSource(string $sourceFilePath): ?RouteContract
    {
        return RouteModel::getFromSource($sourceFilePath);
    }
}
