<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\RouteCollection
 */
class Router extends Facade
{
    public static function getFacadeRoot(): \Hyde\Foundation\RouteCollection
    {
        return HydeKernel::getInstance()->routes();
    }
}
