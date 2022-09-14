<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Hyde;
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
        $this->table(['Route Key', 'Source File', 'Output File', 'Page Type'], $this->getRoutes());

        return 0;
    }

    protected function getRoutes(): array
    {
        $routes = [];
        /** @var \Hyde\Framework\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                $route->getRouteKey(),
                $route->getSourcePath(),
                $route->getOutputPath(),
                $this->formatPageType($route->getPageType()),
            ];
        }
        return $routes;
    }

    protected function formatPageType(string $class): string
    {
        return str_replace('Hyde\\Framework\\Models\\Pages\\', '', $class);
    }
}
