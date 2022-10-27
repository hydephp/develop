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
 * @method static Route|null get(string $routeKey)
 * @method static Route|null getFromKey(string $routeKey)
 * @method static Route|null getFromSource(string $sourceFilePath)
 * @method static Route|null getFromModel(HydePage $page)
 * @method static Route getOrFail(string $routeKey)
 * @method static RouteCollection all()
 * @method static bool exists(string $routeKey)
 * @method static Route|null current()
 * @method static Route|null home()
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
