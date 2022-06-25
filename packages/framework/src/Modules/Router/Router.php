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

    public static function getInstance(): RouterContract
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /** @var Collection<RouteContract> */
    protected Collection $routes;

    /** @var array<string<Concerns\RoutableContract>> */
    protected array $routeModels;

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

    public function getRoute(string $name): RouteContract
    {
        return $this->routes->first(function (RouteContract $route) use ($name) {
            return $route->getName() === $name;
        });

        throw new RouteNotFoundException($name);
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

    /** @param string<Concerns\RoutableContract> $model  */
    public function registerRoutableModel(string $model): void
    {
        $this->routeModels[$model] = true;
    }

    /** @param array<string<Concerns\RoutableContract>> $models */
    public function registerRoutableModels(array $models): void
    {
        foreach ($models as $model) {
            $this->registerRoutableModel($model);
        }
    }


    protected function discoverRoutes(): void {
        foreach ($this->routeModels as $model => $value) {
            $this->discoverRoutesForModel($model);
        }
    }

    /** @param string<Concerns\RoutableContract> $model  */
    protected function discoverRoutesForModel(string $model): void {
        foreach ($model::all() as $file) {
            $this->routes->push($this->discoverAbstract($model, $file));
        }
    }

    /** @param string<Concerns\RoutableContract> $model */
    protected function discoverAbstract(string $model, string $file): RouteContract {
        return new Route($model, $file);
    }
}
