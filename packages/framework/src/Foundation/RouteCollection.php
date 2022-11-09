<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;

/**
 * Pseudo-Router for Hyde.
 *
 * @see \Hyde\Foundation\PageCollection
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 * @see \Hyde\Framework\Testing\Feature\RouteCollectionTest
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
final class RouteCollection extends BaseFoundationCollection
{
    public function getRoutes(?string $pageClass = null): self
    {
        return ! $pageClass ? $this : $this->filter(function (Route $route) use ($pageClass) {
            return $route->getPage() instanceof $pageClass;
        });
    }

    /**
     * This internal method adds the specified route to the route index.
     * It's made public so package developers can hook into the routing system.
     * As a package developer, you will need to make sure your route leads to something.
     */
    public function addRoute(Route $route): self
    {
        $this->put($route->getRouteKey(), $route);

        return $this;
    }

    protected function discover(HydePage $page): self
    {
        // Create a new route for the given page, and add it to the index.
        $this->addRoute(new Route($page));

        return $this;
    }

    protected function runDiscovery(): self
    {
        $this->kernel->pages()->each(function (HydePage $page) {
            $this->discover($page);
        });

        return $this;
    }
}
