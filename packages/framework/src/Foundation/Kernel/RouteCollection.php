<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;

/**
 * The RouteCollection contains all the page routes, making it the pseudo-router for Hyde,
 * as it maps each page to the eventual URL that will be used to access it once built.
 *
 * @template T of \Hyde\Support\Models\Route
 *
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, Route> $items The routes in the collection.
 *
 * @method Route|null get(string $key, Route $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\Router
 * @see \Hyde\Hyde::routes()
 */
final class RouteCollection extends BaseFoundationCollection
{
    public function addRoute(Route $route): void
    {
        $this->put($route->getRouteKey(), $route);
    }

    protected function runDiscovery(): void
    {
        $this->kernel->pages()->each(function (HydePage $page): void {
            $this->addRoute(new Route($page));
        });
    }

    protected function runExtensionHandlers(): void
    {
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverRoutes($this);
        }
    }

    public function getRoute(string $routeKey): Route
    {
        return $this->get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route>
     */
    public function getRoutes(?string $pageClass = null): RouteCollection
    {
        return $pageClass ? $this->filter(function (Route $route) use ($pageClass): bool {
            return $route->getPage() instanceof $pageClass;
        }) : $this;
    }
}
