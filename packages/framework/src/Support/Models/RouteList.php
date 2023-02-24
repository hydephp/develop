<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteListTest
 */
class RouteList implements Arrayable
{
    protected array $routes;

    public function __construct()
    {
        $this->generate();
    }

    public function toArray(): array
    {
        return $this->routes;
    }

    public function headers(): array
    {
        return array_map(function (string $key): string {
            return ucwords(str_replace('_', ' ', $key));
        }, array_keys($this->routes[0]));
    }

    protected function generate(): void
    {
        $routes = [];
        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                'page_type' => $this->stylePageType($route->getPageClass()),
                'source_file' => $this->styleSourcePath($route->getSourcePath(), $route->getPageClass()),
                'output_file' => $this->styleOutputPath($route->getOutputPath()),
                'route_key' => $route->getRouteKey(),
            ];
        }
        $this->routes = $routes;
    }

    protected function stylePageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $class */
    protected function styleSourcePath(string $path, string $class): string
    {
        if (! $class::isDiscoverable()) {
            return 'dynamic';
        }

        return $path;
    }

    protected function styleOutputPath(string $path): string
    {
        return "_site/$path";
    }
}
