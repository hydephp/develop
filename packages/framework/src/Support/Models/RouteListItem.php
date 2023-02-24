<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Facades\Site;
use Illuminate\Contracts\Support\Arrayable;

class RouteListItem implements Arrayable
{
    protected Route $route;

    protected string $pageType;
    protected string $sourceFile;
    protected string $outputFile;
    protected string $routeKey;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function toArray(): array
    {
        return [
            'page_type' => $this->stylePageType($this->route->getPageClass()),
            'source_file' => $this->styleSourcePath($this->route->getSourcePath(), $this->route->getPageClass()),
            'output_file' => $this->styleOutputPath($this->route->getOutputPath()),
            'route_key' => $this->styleRouteKey($this->route->getRouteKey()),
        ];
    }

    protected function stylePageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $class */
    protected function styleSourcePath(string $path, string $class): string
    {
        return $class::isDiscoverable() ? $path : $this->getDynamicSourceLabel();
    }

    protected function styleOutputPath(string $path): string
    {
        return Site::getOutputDirectory()."/$path";
    }

    protected function styleRouteKey(string $key): string
    {
        return $key;
    }

    protected function getDynamicSourceLabel(): string
    {
        return 'dynamic';
    }
}
