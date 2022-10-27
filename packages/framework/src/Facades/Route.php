<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Routing\Router;
use Illuminate\Support\Facades\Facade;

class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
