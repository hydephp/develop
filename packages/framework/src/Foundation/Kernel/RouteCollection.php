<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Foundation\Facades\Routes;
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
 * You would commonly access it via one of the facades:
 *
 * @see \Hyde\Foundation\Facades\Router
 * @see \Hyde\Hyde::routes()
 *
 * This is not a router in the traditional sense that it decides where to go.
 * Instead, it creates a pre-generated object encapsulating the Hyde autodiscovery.
 *
 * This not only let us emulate Laravel route helpers, but also serve as the
 * canonical source of truth for the vital HydePHP autodiscovery process.
 *
 * The routes defined can then also be used to power the RealtimeCompiler without
 * having to reverse-engineer the source file mapping.
 *
 * Routes are not intended to be added manually, instead the route index is created using
 * the exact same rules as the current autodiscovery process and compiled file output.
 * However, extensions can add routes using the discovery handler callbacks.
 *
 * The route index serves as a multidimensional mapping allowing you to
 * determine where a source file will be compiled to, and where a compiled
 * file was generated from. This bridges the gaps between the source and
 * the compiled web accessible URI routes the static site generator creates.
 */
final class RouteCollection extends BaseFoundationCollection
{
    /**
     * This method adds the specified route to the route index.
     * It can be used by package developers to hook into the routing system.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off routes;
     * If you are registering multiple routes, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery.
     *
     * In addition, you might actually rather want to use the PageCollection's addPage method
     * instead as all pages there are automatically also added as routes here as well.
     */
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
