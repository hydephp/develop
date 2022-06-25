<?php

namespace Hyde\Framework\Modules\Router;

use Hyde\Framework\Modules\Router\Concerns\RouteContract;
use Hyde\Framework\Modules\Router\Concerns\RouterContract;
use Illuminate\Support\Collection;

class Router implements RouterContract
{
    protected static RouterContract $instance;

    public static function getInstance(): RouterContract
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /** @var Collection<RouteContract> */
    protected Collection $routes;

    protected function __construct()
    {
        // TODO: Implement __construct() method.
    }


    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function getArray(): array
    {
        return $this->routes->toArray();
    }

    public function getJson(): string
    {
        return $this->routes->toJson();
    }


    protected function discoverRoutes(): void {}

    protected function discoverBladePages(): void {}

    protected function discoverMarkdownPages(): void {}

    protected function discoverMarkdownPosts(): void {}

    protected function discoverDocumentationPages(): void {}

    protected function discoverAbstract(string $className): void {}
}
