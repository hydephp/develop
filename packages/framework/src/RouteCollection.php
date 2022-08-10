<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\PageCollection
 * @see \Hyde\Framework\Testing\Feature\RouteTest
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

    public function getRoutes(): self
    {
        return $this;
    }

    /**
     * @deprecated Will be merged into getRoutes()
     */
    public function getRoutesForModel(string $pageClass): self
    {
        // Return a new filtered collection with only routes that are for the given page class.
        return $this->filter(function (RouteContract $route) use ($pageClass) {
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
