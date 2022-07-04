<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Illuminate\Support\Collection;

/**
 * Pseudo-Router for Hyde
 *
 * This is not a router in the traditional sense that it decides where to go.
 * Instead, it creates a pre-generated object encapsulating the Hyde autodiscovery.
 *
 * If successful, this will not only let us emulate Laravel route helpers, but also
 * serve as the canonical source of truth for the Hyde autodiscovery process.
 *
 * The routes defined can then also be used to power the RealtimeCompiler without
 * having to reverse-engineer the source file mapping.
 *
 * Routes cannot be added manually, instead the route index is created using the
 * exact same rules as the current autodiscovery process and compiled file output.
 *
 * The route index shall serve as a multidimensional mapping allowing you to
 * determine where a source file will be compiled to, and where a compiled
 * file was generated from.
 *
 * @todo Convert into Singleton.
 *       As the constructor needs to load and parse every single page,
 *       this could be a rather expensive operation that would benefit
 *       from being a singleton. It could also be a good idea to cache
 *       the parsed route index using the Laravel cache in the future.
 *
 * @see \Hyde\Framework\Testing\Feature\RouterTest
 */
class Router implements RouterContract
{
    /**
     * The routes discovered by the router.
     *
     * @var \Illuminate\Support\Collection<\Hyde\Framework\Modules\Routing\RouteContract>
     */
    protected Collection $routes;

    /** @inheritDoc */
    public function __construct()
    {
        $this->discoverRoutes();
    }

    /** @inheritDoc */
    public static function getInstance(): RouterContract
    {
        return new static();
    }

    /** @inheritDoc */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    protected function discover(PageContract $page): self
    {
        $route = new Route($page);
        $this->routes->put($route->getRouteKey(), $route);

        return $this;
    }

    protected function discoverRoutes(): self
    {
        $this->routes = new Collection();

        $pages = [
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        foreach ($pages as $page) {
            $this->discoverPageRoutes($page);
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
