<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Facades\Site;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\BuildTasks\PreBuildTasks\CleanSiteDirectory;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function collect;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 *
 * @see \Hyde\Console\Commands\BuildSiteCommand
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
        collect($this->getPageTypes())->each(function (string $pageClass): void {
            $this->compilePagesForClass($pageClass);
        });
    }

    /** @deprecated Will be handled by a build task */
    public function cleanOutputDirectory(): void
    {
        // TODO Register task in service instead
        (new CleanSiteDirectory())->handle();
    }

    public function transferMediaAssets(): void
    {
        $this->needsDirectory(Hyde::siteMediaPath());

        $this->comment('Transferring Media Assets...');
        $this->withProgressBar(DiscoveryService::getMediaAssetFiles(), function (string $filepath): void {
            $sitePath = Hyde::siteMediaPath(Str::after($filepath, Hyde::mediaPath()));
            $this->needsParentDirectory($sitePath);
            copy($filepath, $sitePath);
        });

        $this->newLine(2);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     */
    protected function compilePagesForClass(string $pageClass): void
    {
        $this->comment("Creating {$this->getClassPluralName($pageClass)}...");

        $collection = $this->router->getRoutes($pageClass);

        $this->withProgressBar($collection, function (Route $route): void {
            (new StaticPageBuilder($route->getPage()))->__invoke();
        });

        $this->newLine(2);
    }

    protected function getClassPluralName(string $pageClass): string
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
        return in_array(basename(Hyde::sitePath()), $this->safeOutputDirectories());
    }

    protected function askIfUnsafeDirectoryShouldBeEmptied(): bool
    {
        return $this->confirm(sprintf(
            'The configured output directory (%s) is potentially unsafe to empty. '.
            'Are you sure you want to continue?',
            Site::getOutputDirectory()
        ));
    }

    protected function safeOutputDirectories(): array
    {
        return config('hyde.safe_output_directories', ['_site', 'docs', 'build']);
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    protected function getPageTypes(): array
    {
        return Hyde::pages()->map(function (HydePage $page): string {
            return $page::class;
        })->unique()->values()->toArray();
    }
}
