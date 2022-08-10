<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

/**
 * @deprecated v0.59.0-beta Use new RouteCollection instead.
 */
interface RoutingServiceContract
{
    /**
     * Get the Singleton instance of the Router.
     *
     * @return \Hyde\Framework\Contracts\RoutingServiceContract
     */
    public static function getInstance(): RoutingServiceContract;

    /**
     * Get the routes discovered by the router.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutes(): Collection;

    /**
     * Get all discovered routes for the given page class.
     *
     * @param  class-string<\Hyde\Framework\Contracts\PageContract>  $pageClass
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutesForModel(string $pageClass): Collection;

    /**
     * Add a route to the router index.
     *
     * This internal method adds the specified route to the route index.
     * It's intended to be used for package developers to hook into the routing system.
     *
     * @param  \Hyde\Framework\Contracts\RouteContract  $route
     * @return $this
     */
    public function addRoute(RouteContract $route): self;
}
