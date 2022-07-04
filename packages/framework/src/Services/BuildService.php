<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Routing\RouteContract as Route;
use Hyde\Framework\Modules\Routing\Router;
use Hyde\Framework\StaticPageBuilder;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 */
class BuildService
{
    use InteractsWithIO;
    use InteractsWithDirectories;

    protected Router $router;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;

        $this->router = Router::getInstance();
    }

    public function compileStaticPages(): void
    {
        $this->getDiscoveredModels()->each(function (string $pageClass) {
            $this->compilePages($pageClass);
        });
    }

    public function transferMediaAssets(): void
    {
        $this->needsDirectory(Hyde::getSiteOutputPath('media'));

        $collection = CollectionService::getMediaAssetFiles();
        if ($this->canRunBuildAction($collection, 'Media Assets', 'Transferring')) {
            $this->withProgressBar(
                $collection,
                function ($filepath) {
                    copy($filepath, Hyde::getSiteOutputPath('media/'.basename($filepath)));
                }
            );
            $this->newLine(2);
        }
    }

    protected function getDiscoveredModels(): Collection
    {
        return $this->router->getRoutes()->map(function (Route $route) {
            return $route->getPageType();
        })->unique();
    }

    protected function canRunBuildAction(array|\Countable $collection, string $pageClass, ?string $verb = null): bool
    {
        $name = $this->getModelPluralName($pageClass);

        if (sizeof($collection) < 1) {
            $this->line("No $name found. Skipping...\n");
            return false;
        }

        $this->comment(($verb ?? 'Creating')." $name...");
        return true;
    }

    protected function compilePages(string $pageClass): void
    {
        $collection = $this->router->getRoutesForModel($pageClass);

        if ($this->canRunBuildAction($collection, $pageClass)) {
            $this->withProgressBar(
                $collection, $this->compileRoute()
            );
            $this->newLine(2);
        }
    }

    protected function compileRoute(): \Closure
    {
        return function (Route $route) {
            return (new StaticPageBuilder($route->getSourceModel()))->__invoke();
        };
    }

    protected function getModelPluralName(string $pageClass): string
    {
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($pageClass)).'s';
    }
}
