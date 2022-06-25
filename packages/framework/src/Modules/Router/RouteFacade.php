<?php

namespace Hyde\Framework\Modules\Router;

use Hyde\Framework\Modules\Router\Concerns\RouteContract;
use Hyde\Framework\Modules\Router\Concerns\RouteFacadeContract;
use Illuminate\Support\Collection;

class RouteFacade implements RouteFacadeContract
{
    public static function get(string $key): RouteContract
    {
        return Router::getInstance()->getRoute($key);
    }

    public static function json(): string
    {
        return Router::getInstance()->getJson();
    }

    public static function array(): array
    {
        return Router::getInstance()->getArray();
    }

    public static function collection(): Collection
    {
        return Router::getInstance()->getRoutes();
    }
}