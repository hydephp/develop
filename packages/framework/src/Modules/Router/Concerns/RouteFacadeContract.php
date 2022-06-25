<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

interface RouteFacadeContract
{
    public static function json(): string;
    public static function array(): array;
    public static function collection(): Collection;

    /** @throws \Hyde\Framework\Exceptions\Modules\Router\RouteNotFoundException */
    public static function get(string $key): RouteContract;
}
