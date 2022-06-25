<?php

namespace Hyde\Framework\Modules\Router;

use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Modules\Router\Concerns\RouteContract;
use Hyde\Framework\Modules\Router\Concerns\RouterContract;
use Illuminate\Support\Collection;

class Router implements RouterContract
{
    protected static RouterContract $instance;

    protected static array $routeModels;

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
        $this->registerRoutableModels([
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ]);
        
        $this->discoverRoutes();
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

    /** @param string<Concerns\Routable> $model  */
    public function registerRoutableModel(string $model): void
    {
        static::$routeModels[$model] = true;
    }

    /** @param array<string<Concerns\Routable>> $models */
    public function registerRoutableModels(array $models): void
    {
        foreach ($models as $model) {
            $this->registerRoutableModel($model);
        }
    }

    protected function discoverRoutes(): void {}

    protected function discoverBladePages(): void {}

    protected function discoverMarkdownPages(): void {}

    protected function discoverMarkdownPosts(): void {}

    protected function discoverDocumentationPages(): void {}

    protected function discoverAbstract(string $className): void {}
}
