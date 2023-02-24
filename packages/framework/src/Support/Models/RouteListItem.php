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

        $this->pageType = $this->stylePageType($this->route->getPageClass());
        $this->sourceFile = $this->styleSourcePath($this->route->getSourcePath());
        $this->outputFile = $this->styleOutputPath($this->route->getOutputPath());
        $this->routeKey = $this->styleRouteKey($this->route->getRouteKey());
    }

    public function toArray(): array
    {
        return [
            'page_type' => $this->pageType,
            'source_file' => $this->sourceFile,
            'output_file' => $this->outputFile,
            'route_key' => $this->routeKey,
        ];
    }

    protected function stylePageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    protected function styleSourcePath(string $path): string
    {
        if (! $this->route->getPageClass()::isDiscoverable()) {
            return 'dynamic';
        }

        return $path;
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
