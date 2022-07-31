<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Contracts\RoutingServiceContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Collection;

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
 *
 * @see \Hyde\Framework\Testing\Feature\RoutingServiceTest
 */
class RoutingService implements RoutingServiceContract
{
    /**
     * The routes discovered by the router.
     *
     * @var \Illuminate\Support\Collection<\Hyde\Framework\Contracts\RouteContract>
     */
    protected Collection $routes;

    /**
     * @var \Hyde\Framework\Services\RoutingService|null The singleton instance of the router.
     */
    protected static RoutingService|null $instance = null;

    /** @inheritDoc */
    public function __construct()
    {
        $this->discoverRoutes();
    }

    /** @inheritDoc */
    public static function getInstance(): self
    {
        // This creates a singleton pattern. When testing we always create a new instance.
        if (static::$instance === null || app()->environment('testing')) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /** @inheritDoc */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /** @inheritDoc */
    public function getRoutesForModel(string $pageClass): Collection
    {
        // Return a new filtered collection with only routes that are for the given page class.
        return $this->routes->filter(function (RouteContract $route) use ($pageClass) {
            return $route->getSourceModel() instanceof $pageClass;
        });
    }

    /**
     * This internal method adds the specified route to the route index.
     * It's made publics so package developers can hook into the routing system.
     */
    public function addRoute(RouteContract $route): self
    {
        $this->routes->put($route->getRouteKey(), $route);

        return $this;
    }

    protected function discover(PageContract $page): static
    {
        // Create a new route for the given page, and add it to the index.
        $this->addRoute(new Route($page));

        return $this;
    }

    protected function discoverRoutes(): static
    {
        $this->routes = new Collection();

        if (Features::hasBladePages()) {
            $this->discoverPageRoutes(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->discoverPageRoutes(MarkdownPage::class);
        }

        if (Features::hasBlogPosts()) {
            $this->discoverPageRoutes(MarkdownPost::class);
        }

        if (Features::hasDocumentationPages()) {
            $this->discoverPageRoutes(DocumentationPage::class);
        }

        return $this;
    }

    protected function discoverPageRoutes(string $pageClass): void
    {
        /** @var PageContract $pageClass */
        $pageClass::all()->each(function ($page) {
            $this->discover($page);
        });
    }
}
