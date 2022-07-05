<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Modules\Routing\Route as RouteModel;

/**
 * @see \Hyde\Framework\Modules\Routing\Route
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
     * Same as static::get(), but throws an exception if the route key is not found.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract
     *
     * @throws \Hyde\Framework\Modules\Routing\RouteNotFoundException
     */
    public static function getOrFail(string $routeKey): RouteContract
    {
        return RouteModel::getOrFail($routeKey);
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

    /**
     * Same as static::getFromSource(), but throws an exception if the source file path is not found.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract
     *
     * @throws \Hyde\Framework\Modules\Routing\RouteNotFoundException
     */
    public static function getFromSourceOrFail(string $sourceFilePath): RouteContract
    {
        return RouteModel::getFromSourceOrFail($sourceFilePath);
    }
}
