<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Contracts\RouteContract as Route;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Hyde;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticSiteServiceTest
 */
class BuildService
{
    use InteractsWithIO;
    use InteractsWithDirectories;

    protected RouteCollection $router;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;

        $this->router = Hyde::routes();
    }

    public function compileStaticPages(): void
    {
        $this->getDiscoveredModels()->each(function (string $pageClass) {
            $this->compilePagesForClass($pageClass);
        });
    }

    public function cleanOutputDirectory(): void
    {
        if (config('hyde.empty_output_directory', true)) {
            $this->warn('Removing all files from build directory.');

            if ($this->isItSafeToCleanOutputDirectory()) {
                array_map('unlink', glob(Hyde::getSiteOutputPath('*.{html,json}'), GLOB_BRACE));
                File::cleanDirectory(Hyde::getSiteOutputPath('media'));
            }
        }
    }

    public function transferMediaAssets(): void
    {
        $this->needsDirectory(Hyde::getSiteOutputPath('media'));

        $collection = DiscoveryService::getMediaAssetFiles();
        $this->comment('Transferring Media Assets...');

        $this->withProgressBar(
            $collection,
            function ($filepath) {
                copy($filepath, Hyde::getSiteOutputPath('media/'.basename($filepath)));
            }
        );
        $this->newLine(2);
    }

    /**
     * @return \Hyde\Framework\Foundation\RouteCollection<array-key, class-string<\Hyde\Framework\Contracts\PageContract>>
     */
    protected function getDiscoveredModels(): RouteCollection
    {
        return $this->router->getRoutes()->map(function (Route $route) {
            return $route->getPageType();
        })->unique();
    }

    protected function compilePagesForClass(string $pageClass): void
    {
        $this->comment("Creating {$this->getModelPluralName($pageClass)}...");

        $collection = $this->router->getRoutes($pageClass);

        $this->withProgressBar(
            $collection,
            $this->compileRoute()
        );

        $this->newLine(2);
    }

    /** @psalm-return \Closure(Route):string */
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

    protected function isItSafeToCleanOutputDirectory(): bool
    {
        if (! $this->isOutputDirectoryWhitelisted() && ! $this->askIfUnsafeDirectoryShouldBeEmptied()) {
            $this->info('Output directory will not be emptied.');

            return false;
        }

        return true;
    }

    protected function isOutputDirectoryWhitelisted(): bool
    {
        return in_array(
            basename(Hyde::getSiteOutputPath()),
            config('hyde.safe_output_directories', ['_site', 'docs', 'build'])
        );
    }

    protected function askIfUnsafeDirectoryShouldBeEmptied(): bool
    {
        return $this->confirm(sprintf(
            'The configured output directory (%s) is potentially unsafe to empty. '.
            'Are you sure you want to continue?',
            StaticPageBuilder::$outputPath
        ));
    }
}
