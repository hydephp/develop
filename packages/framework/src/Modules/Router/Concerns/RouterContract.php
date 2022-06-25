<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

interface RouterContract
{
    public static function getInstance(): RouterContract;

    /** @param string<RoutableContract> $model  */
    public function registerRoutableModel(string $model): void;

    public function getRoute(string $name): RouteContract;
    public function getRoutes(): Collection;
    public function getArray():  array;
    public function getJson(): string;
}
