<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Contracts\RoutingServiceContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Collection;

/**
 * Pseudo-Router for Hyde.
 *
 * @deprecated Use Hyde::routes() instead.
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
 *
 * @see \Hyde\Framework\Testing\Feature\RoutingServiceTest
 */
class RoutingService implements RoutingServiceContract
{
    /** @inheritDoc */
    public function __construct()
    {
    }

    /** @inheritDoc */
    public static function getInstance(): self
    {
        return new self();
    }

    /** @inheritDoc */
    public function getRoutes(): Collection
    {
        return Hyde::routes();
    }

    /** @inheritDoc */
    public function getRoutesForModel(string $pageClass): Collection
    {
        // Return a new filtered collection with only routes that are for the given page class.
        return $this->getRoutes()->filter(function (RouteContract $route) use ($pageClass) {
            return $route->getSourceModel() instanceof $pageClass;
        });
    }

    public function addRoute(RouteContract $route): self
    {
        return $this;
    }
}
