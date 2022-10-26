<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\DiscoveryService;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to display the list of site routes.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeRouteListCommandTest
 */
class HydeRouteListCommand extends Command
{
    protected $signature = 'route:list';
    protected $description = 'Display all registered routes.';

    public function handle(): int
    {
        $this->table([
            'Page Type',
            'Source File',
            'Output File',
            'Route Key',
        ], $this->getRoutes());

        return 0;
    }

    protected function getRoutes(): array
    {
        $routes = [];
        /** @var \Hyde\Framework\Support\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                $this->formatPageType($route->getPageType()),
                $this->formatSourcePath($route->getSourcePath()),
                $this->formatOutputPath($route->getOutputPath()),
                $route->getRouteKey(),
            ];
        }

        return $routes;
    }

    protected function formatPageType(string $class): string
    {
        return str_replace('Hyde\\Framework\\Models\\Pages\\', '', $class);
    }

    protected function formatSourcePath(string $path): string
    {
        return $this->clickablePathLink(DiscoveryService::createClickableFilepath(Hyde::path($path)), $path);
    }

    protected function formatOutputPath(string $path): string
    {
        if (! file_exists(Hyde::sitePath($path))) {
            return "_site/$path";
        }

        return $this->clickablePathLink(DiscoveryService::createClickableFilepath(Hyde::sitePath($path)), "_site/$path");
    }

    protected function clickablePathLink(string $link, string $path): string
    {
        return "<href=$link>$path</>";
    }
}
