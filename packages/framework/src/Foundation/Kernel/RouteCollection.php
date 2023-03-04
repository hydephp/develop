<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;

/**
 * The RouteCollection contains all the routes, making it the Pseudo-Router for Hyde.
 *
 * @template T of \Hyde\Support\Models\Route
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, Route> $items The routes in the collection.
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

    protected function runExtensionCallbacks(): void
    {
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverRoutes($this);
        }
    }
}
