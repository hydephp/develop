<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Contracts\RoutingServiceContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\RouteCollection;

/**
 * Pseudo-Router for Hyde.
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
 *
 * @see \Hyde\Framework\Testing\Feature\RoutingServiceTest
 */
class RoutingService implements RoutingServiceContract
{
    /**
     * @deprecated
     * @inheritDoc
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Get all routes discovered by the autodiscovery process.
     *
     * @return \Hyde\Framework\RouteCollection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutes(): RouteCollection
    {
        return Hyde::routes();
    }

    /**
     * Get all discovered routes for the given page class.
     *
     * @param  class-string<\Hyde\Framework\Contracts\PageContract>  $pageClass
     * @return \Hyde\Framework\RouteCollection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutesForModel(string $pageClass): RouteCollection
    {
        return Hyde::routes()->getRoutes($pageClass);
    }

    /**
     * Add a route to the router index.
     *
     * This internal method adds the specified route to the route index.
     * It's intended to be used for package developers to hook into the routing system.
     *
     * @param  \Hyde\Framework\Contracts\RouteContract  $route
     * @return $this
     */
    public function addRoute(RouteContract $route): static
    {
        Hyde::routes()->addRoute($route);

        return $this;
    }
}
