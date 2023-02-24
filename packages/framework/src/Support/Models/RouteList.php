<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Hyde;
use Hyde\Facades\Site;
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
                'route_key' => $this->styleRouteKey($route->getRouteKey()),
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
        return $class::isDiscoverable() ? $path : 'dynamic';
    }

    protected function styleOutputPath(string $path): string
    {
        return Site::getOutputDirectory()."/$path";
    }

    protected function styleRouteKey(string $key): string
    {
        return $key;
    }
}
