<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;

/**
 * The RouteCollection contains all the routes, making it the Pseudo-Router for Hyde.
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
    public function getRoute(string $routeKey): Route
    {
        return $this->items[$routeKey] ?? throw new RouteNotFoundException($routeKey.' in route collection');
    }

    public function getRoutes(?string $pageClass = null): self
    {
        return ! $pageClass ? $this : $this->filter(function (Route $route) use ($pageClass): bool {
            return $route->getPage() instanceof $pageClass;
        });
    }

    /**
     * This method adds the specified route to the route index.
     * It can be used by package developers to hook into the routing system.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off routes;
     * If you are registering multiple routes, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery. In addition,
     * you might actually rather want to use the page collection's addPage method instead,
     * as all pages there are automatically also added as routes here as well.
     *
     * When using this method, take notice of the following things:
     * 1. Be sure to register the route before the HydeKernel boots.
     * 2. Make sure the route leads to something that can be compiled.
     */
    public function addRoute(Route $route): self
    {
        $this->put($route->getRouteKey(), $route);

        return $this;
    }

    protected function runDiscovery(): self
    {
        $this->kernel->pages()->each(function (HydePage $page): void {
            $this->discover($page);
        });

        $this->runExtensionCallbacks();

        return $this;
    }

    protected function runExtensionCallbacks(): self
    {
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverRoutes($this);
        }

        return $this;
    }

    protected function discover(HydePage $page): void
    {
        $this->addRoute(new Route($page));
    }
}
