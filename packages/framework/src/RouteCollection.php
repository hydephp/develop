<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Collection;

/**
 * Pseudo-Router for Hyde.

 * @see \Hyde\Framework\PageCollection
 * @see \Hyde\Framework\Testing\Feature\RouteTest
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
 *
 * The route index serves as a multidimensional mapping allowing you to
 * determine where a source file will be compiled to, and where a compiled
 * file was generated from.
 */
final class RouteCollection extends Collection
{
    protected HydeKernelContract $kernel;

    public static function boot(HydeKernelContract $kernel): self
    {
        return (new self())->setKernel($kernel)->discoverRoutes();
    }

    protected function __construct($items = [])
    {
        parent::__construct($items);
    }

    protected function setKernel(HydeKernelContract $kernel): self
    {
        $this->kernel = $kernel;

        return $this;
    }

    public function getRoutes(?string $pageClass = null): self
    {
        return ! $pageClass ? $this : $this->filter(function (RouteContract $route) use ($pageClass) {
            return $route->getSourceModel() instanceof $pageClass;
        });
    }

    /**
     * This internal method adds the specified route to the route index.
     * It's made public so package developers can hook into the routing system.
     */
    public function addRoute(RouteContract $route): self
    {
        $this->put($route->getRouteKey(), $route);

        return $this;
    }

    protected function discover(PageContract $page): self
    {
        // Create a new route for the given page, and add it to the index.
        $this->addRoute(new Route($page));

        return $this;
    }

    protected function discoverRoutes(): self
    {
        $this->kernel->pages()->each(function (PageContract $page) {
            $this->discover($page);
        });

        return $this;
    }
}
