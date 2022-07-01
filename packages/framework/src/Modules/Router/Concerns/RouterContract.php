<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

/**
 * General note for all Route related Contracts while in development:.
 *
 * My intention is for these contracts to allow Hyde to be easily extended.
 * Just know, that the contracts may at this point not cover everything they should.
 * As a package developer, you should look at how Hyde defines models to understand
 * what your extension needs. And please, submit a PR to update the interfaces with
 * anything missing. Thank you. Oh, and remember that in 0.x anything may change.
 */
interface RouterContract
{
    public static function getInstance(): RouterContract;

    /** @param array<string<RoutableContract>> $models */
    public function registerRoutableModels(array $models): void;

    /** @param string<RoutableContract> $model  */
    public function registerRoutableModel(string $model): void;

    public function getRoute(string $name): RouteContract;

    public function getRoutes(): Collection;

    public function getArray(): array;

    public function getJson(): string;
}
