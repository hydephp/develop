<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\RouteCollection;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\RouteCollection
 */
class Router extends Facade
{
    public static function getFacadeRoot(): RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }
}
