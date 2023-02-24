<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Facades\Site;
use Hyde\Pages\InMemoryPage;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @internal This class is experimental and is subject to change.
 * @experimental This class is experimental and is subject to change.
 */
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

        $this->pageType = $this->stylePageType($route->getPageClass());
        $this->sourceFile = $this->styleSourcePath($route->getSourcePath());
        $this->outputFile = $this->styleOutputPath($route->getOutputPath());
        $this->routeKey = $this->styleRouteKey($route->getRouteKey());
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
        if (! $this->isPageDiscoverable()) {
            return 'none';
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

    protected function isPageDiscoverable(): bool
    {
        return $this->route->getSourcePath() && ! $this->route->getPage() instanceof InMemoryPage;
    }
}
