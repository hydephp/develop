<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\RouteCollection;
use Hyde\Routing\Router;
use Illuminate\Support\Facades\Facade;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 *
 * @see \Hyde\Routing\Router
 *
 * @method static ?Route get(string $routeKey)
 * @method static ?Route getFromKey(string $routeKey)
 * @method static ?Route getFromSource(string $sourceFilePath)
 * @method static ?Route getFromModel(HydePage $page)
 * @method static Route getOrFail(string $routeKey)
 * @method static RouteCollection all()
 * @method static bool exists(string $routeKey)
 * @method static ?Route current()
 * @method static ?Route home()
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
