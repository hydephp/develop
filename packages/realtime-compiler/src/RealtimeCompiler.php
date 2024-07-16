<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Desilva\Microserve\Response;

class RealtimeCompiler
{
    /** @var array<string, callable(): Response> */
    protected array $virtualRoutes = [];

    /** @param callable(): Response $route */
    public function registerVirtualRoute(string $uri, callable $route): void
    {
        $this->virtualRoutes[$uri] = $route;
    }

    /** @return array<string, callable(): Response> */
    public function getVirtualRoutes(): array
    {
        return $this->virtualRoutes;
    }
}
