<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\RouteCollection;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Routing\Router;
use Illuminate\Support\Facades\Facade;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 *
 * @see \Hyde\Routing\Router
 *
 * @method static \Hyde\Routing\Route|null home()
 * @method static \Hyde\Routing\Route|null current()
 * @method static \Hyde\Routing\Route|null get(string $routeKey)
 * @method static \Hyde\Routing\Route|null getFromKey(string $routeKey)
 * @method static \Hyde\Routing\Route|null getFromSource(string $sourceFilePath)
 * @method static \Hyde\Routing\Route|null getFromModel(HydePage $page)
 * @method static \Hyde\Routing\Route getOrFail(string $routeKey)
 * @method static bool exists(string $routeKey)
 * @method static RouteCollection all()
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
