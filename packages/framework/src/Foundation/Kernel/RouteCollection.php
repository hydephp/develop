<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;

/**
 * The RouteCollection contains all the routes, making it the Pseudo-Router for Hyde.
 *
 * @template T of \Hyde\Support\Models\Route
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, Route> $items The routes in the collection.
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via one of the facades:
 *
 * @see \Hyde\Foundation\Facades\Router
 * @see \Hyde\Hyde::routes()
 *
 * The HydePHP Pseudo-Router provides an object that encapsulates the Hyde
 * autodiscovery process. It serves as a canonical source of truth for the
 * autodiscovery process and allows emulation of Laravel route helpers.
 *
 * The route index serves as a multidimensional mapping, which helps determine
 * where a source file will be compiled to and where a compiled file was generated
 * from. This feature bridges the gaps between the source and the compiled
 * web-accessible URI routes created by the static site generator.
 *
 * The routes are integral to the build process, as each route contains the
 * information needed to compile the connected source file to a static page with
 * the correct destination.
 *
 * The defined routes can power the RealtimeCompiler, eliminating the need to
 * reverse-engineer source file mapping. This integration provides seamless and
 * efficient compilation of routes.
 *
 * Manually adding routes is not recommended. Instead, the route index is created
 * using the exact same rules as the current autodiscovery process and compiled
 * file output. However, extensions can add routes using the discovery handler
 * callbacks.
 */
final class RouteCollection extends BaseFoundationCollection
{
    /**
     * This method adds the specified route to the route index.
     * It can be used by package developers to hook into the routing system.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off routes;
     * If you are registering multiple routes, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery.
     *
     * In addition, you might actually rather want to use the PageCollection's addPage method
     * instead as all pages there are automatically also added as routes here as well.
     */
    public function addRoute(Route $route): void
    {
        $this->put($route->getRouteKey(), $route);
    }

    protected function runDiscovery(): void
    {
        $this->kernel->pages()->each(function (HydePage $page): void {
            $this->addRoute(new Route($page));
        });
    }

    protected function runExtensionCallbacks(): void
    {
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverRoutes($this);
        }
    }
}
