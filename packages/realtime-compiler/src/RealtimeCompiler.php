<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Hyde\Support\Models\Route;

class RealtimeCompiler
{
    /**
     * @var array<string, Route>
     */
    private array $virtualRoutes = [];

    /**
     * Register a virtual route.
     *
     * @param  string  $uri  The URI of the virtual route
     * @param  Route  $route  The Route object
     */
    public function registerVirtualRoute(string $uri, Route $route): void
    {
        $this->virtualRoutes[$uri] = $route;
    }

    /**
     * Get all registered virtual routes.
     *
     * @return array<string, Route>
     */
    public function getVirtualRoutes(): array
    {
        return $this->virtualRoutes;
    }
}
