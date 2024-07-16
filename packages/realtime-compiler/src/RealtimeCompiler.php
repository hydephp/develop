<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Hyde\Support\Models\Route;

class RealtimeCompiler
{
    /** @var array<string, Route> */
    private array $virtualRoutes = [];

    public function registerVirtualRoute(string $uri, Route $route): void
    {
        $this->virtualRoutes[$uri] = $route;
    }

    /** @return array<string, Route> */
    public function getVirtualRoutes(): array
    {
        return $this->virtualRoutes;
    }
}
