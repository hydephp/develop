<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteListTest
 */
class RouteList implements Arrayable
{
    protected bool $runningInConsole = false;

    public function runningInConsole(bool $runningInConsole = true): static
    {
        $this->runningInConsole = $runningInConsole;

        return $this;
    }

    public function toArray(): array
    {
        $routes = [];
        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                $this->formatPageType($route->getPageClass()),
                $this->formatSourcePath($route->getSourcePath(), $route->getPageClass()),
                $this->formatOutputPath($route->getOutputPath()),
                $route->getRouteKey(),
            ];
        }

        return $routes;
    }

    protected function formatPageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $class */
    protected function formatSourcePath(string $path, string $class): string
    {
        if (! $class::isDiscoverable()) {
            return '<fg=yellow>dynamic</>';
        }

        if ($this->runningInConsole) {
            return $this->clickablePathLink(Command::createClickableFilepath(Hyde::path($path)), $path);
        }

        return $path;
    }

    protected function formatOutputPath(string $path): string
    {
        if (! file_exists(Hyde::sitePath($path))) {
            return "_site/$path";
        }

        if ($this->runningInConsole) {
            return $this->clickablePathLink(Command::createClickableFilepath(Hyde::sitePath($path)), "_site/$path");
        }

        return "_site/$path";
    }

    protected function clickablePathLink(string $link, string $path): string
    {
        return "<href=$link>$path</>";
    }
}
