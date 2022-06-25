<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

interface RouteFacadeContract
{
    /**
     * Get a Route by its generated name in dot notation.
     *
     * @example Route::get('pages.about')
     *
     * @throws \Hyde\Framework\Modules\Router\RouteNotFoundException
     */
    public static function get(string $key): RouteContract;

    public static function json(): string;

    public static function array(): array;

    public static function collection(): Collection;
}
