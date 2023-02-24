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
    protected bool $styleForConsole = false;

    public function styleForConsole(bool $styleForConsole = true): static
    {
        $this->styleForConsole = $styleForConsole;

        return $this;
    }

    public function toArray(): array
    {
        $routes = [];
        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                'Page Type' => $this->formatPageType($route->getPageClass()),
                'Source File' => $this->formatSourcePath($route->getSourcePath(), $route->getPageClass()),
                'Output File' => $this->formatOutputPath($route->getOutputPath()),
                'Route Key' => $route->getRouteKey(),
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

        if ($this->styleForConsole) {
            return $this->clickablePathLink(Command::createClickableFilepath(Hyde::path($path)), $path);
        }

        return $path;
    }

    protected function formatOutputPath(string $path): string
    {
        if ($this->styleForConsole && file_exists(Hyde::sitePath($path))) {
            return $this->clickablePathLink(Command::createClickableFilepath(Hyde::sitePath($path)), "_site/$path");
        }

        return "_site/$path";
    }

    protected function clickablePathLink(string $link, string $path): string
    {
        return "<href=$link>$path</>";
    }
}
