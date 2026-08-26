<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Facades\Localization;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Redirect;
use Hyde\Support\Models\Route;

/**
 * The RouteCollection contains all the page routes, making it the pseudo-router for Hyde,
 * as it maps each page to the eventual URL that will be used to access it once built.
 *
 * @template T of \Hyde\Support\Models\Route
 *
 * @extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, Route> $items The routes in the collection.
 *
 * @method Route|null get(string $key, Route $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\Router
 * @see \Hyde\Hyde::routes()
 *
 * This is also the layer where localization happens. A source file is always exactly one
 * page, but a localized site emits one file per configured language, so each page is
 * projected into one route per language, each routed to its own language directory.
 */
final class RouteCollection extends BaseFoundationCollection
{
    /**
     * Add a route to the collection.
     *
     * When the site is localized, the route is expanded into one route per configured
     * language instead, so that pages added by extensions are localized as well.
     */
    public function addRoute(Route $route): void
    {
        foreach ($this->localizeRoute($route) as $localized) {
            $this->put($localized->getRouteKey(), $localized);
        }
    }

    protected function runDiscovery(): void
    {
        $this->kernel->pages()->each(function (HydePage $page): void {
            $this->addRoute(new Route($page));
        });

        if (Localization::enabled()) {
            $this->addDefaultLanguageRedirect();
        }
    }

    protected function runExtensionHandlers(): void
    {
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverRoutes($this);
        }
    }

    /**
     * Expand a route into one route per configured language, or return it as is
     * when the site is not localized, or the route is already localized.
     *
     * @return array<Route>
     */
    protected function localizeRoute(Route $route): array
    {
        if (! Localization::enabled() || $route->getPage()->getLanguage() !== null) {
            return [$route];
        }

        return array_map(function (string $language) use ($route): Route {
            return $route->forLanguage($language);
        }, Localization::languages());
    }

    /**
     * Add a redirect from the site webroot to the default language, so that visitors
     * landing on `/` are sent to `/en/` when English is the default language.
     *
     * The redirect is put directly into the collection, as it is a routing artifact
     * that belongs to the webroot, and so must not be localized in turn.
     */
    protected function addDefaultLanguageRedirect(): void
    {
        $redirect = new Redirect('index', Localization::defaultLanguage().'/', matter: [
            'navigation' => ['hidden' => true],
        ]);

        $this->put($redirect->getRouteKey(), new Route($redirect));
    }

    public function getRoute(string $routeKey): Route
    {
        return $this->get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass */
    public function getRoutes(?string $pageClass = null): RouteCollection
    {
        return $pageClass ? $this->filter(function (Route $route) use ($pageClass): bool {
            return $route->getPage() instanceof $pageClass;
        }) : $this;
    }
}
