<?php

namespace Hyde\Framework\Modules\Router;

use Hyde\Framework\Modules\Router\Concerns\RouteContract;
use Hyde\Framework\Modules\Router\Concerns\RouteFacadeContract;
use Illuminate\Support\Collection;

class RouteFacade implements RouteFacadeContract
{
    public static function get(string $key): RouteContract
    {
        // TODO: Implement get() method.
    }

    public static function json(): string
    {
        // TODO: Implement json() method.
    }

    public static function array(): array
    {
        // TODO: Implement array() method.
    }

    public static function collection(): Collection
    {
        // TODO: Implement collection() method.
    }
}