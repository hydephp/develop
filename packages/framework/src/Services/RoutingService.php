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
 * Routes cannot be added manually, instead the route index is created using the
 * exact same rules as the current autodiscovery process and compiled file output.
 *
 * The route index serves as a multidimensional mapping allowing you to
 * determine where a source file will be compiled to, and where a compiled
 * file was generated from.
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

    /** @inheritDoc */
    public function getRoutes(): RouteCollection
    {
        return Hyde::routes();
    }

    /** @inheritDoc */
    public function getRoutesForModel(string $pageClass): RouteCollection
    {
        return Hyde::routes()->getRoutesForModel($pageClass);
    }

    public function addRoute(RouteContract $route): self
    {
        Hyde::routes()->addRoute($route);

        return $this;
    }
}
