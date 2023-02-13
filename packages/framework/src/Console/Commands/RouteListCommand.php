<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;

/**
 * Hyde command to display the list of site routes.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\RouteListCommandTest
 */
class RouteListCommand extends Command
{
    /** @var string */
    protected $signature = 'route:list';

    /** @var string */
    protected $description = 'Display all registered routes.';

    public function handle(): int
    {
        $this->table([
            'Page Type',
            'Source File',
            'Output File',
            'Route Key',
        ], $this->getRoutes());

        return Command::SUCCESS;
    }

    protected function getRoutes(): array
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

        return $this->clickablePathLink(static::createClickableFilepath(Hyde::path($path)), $path);
    }

    protected function formatOutputPath(string $path): string
    {
        if (! file_exists(Hyde::sitePath($path))) {
            return "_site/$path";
        }

        return $this->clickablePathLink(static::createClickableFilepath(Hyde::sitePath($path)), "_site/$path");
    }

    protected function clickablePathLink(string $link, string $path): string
    {
        return "<href=$link>$path</>";
    }
}
