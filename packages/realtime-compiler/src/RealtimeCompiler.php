<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Desilva\Microserve\Response;

class RealtimeCompiler
{
    /** @var array<string, Response> */
    private array $virtualRoutes = [];

    public function registerVirtualRoute(string $uri, Response $route): void
    {
        $this->virtualRoutes[$uri] = $route;
    }

    /** @return array<string, Response> */
    public function getVirtualRoutes(): array
    {
        return $this->virtualRoutes;
    }
}
