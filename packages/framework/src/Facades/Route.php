<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Routing\Router;
use Illuminate\Support\Facades\Facade;

/**
 * Provides an easy way to access the Hyde pseudo-router.
 *
 * @see \Hyde\Routing\Router
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
