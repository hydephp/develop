<?php

namespace Hyde\Framework\Modules\Router;

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

    protected function __construct()
    {
        // TODO: Implement __construct() method.
    }

    
    public function getRoutes(): Collection
    {
        // TODO: Implement getRoutes() method.
    }

    public function getArray(): array
    {
        // TODO: Implement getArray() method.
    }

    public function getJson(): string
    {
        // TODO: Implement getJson() method.
    }


    protected function discoverRoutes(): void {}

    protected function discoverBladePages(): void {}

    protected function discoverMarkdownPages(): void {}

    protected function discoverMarkdownPosts(): void {}

    protected function discoverDocumentationPages(): void {}

    protected function discoverAbstract(string $className): void {}
}
