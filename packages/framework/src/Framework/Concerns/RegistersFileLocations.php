<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Facades\Site;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;

/**
 * This trait registers the file paths for important Hyde locations.
 *
 * If you want to customize these directories, the recommended way is to
 * create a service provider that uses this trait, and change your
 * paths in the register method, like in the HydeServiceProvider.
 *
 * Remember that your overriding provider should be loaded after the HSP.
 */
trait RegistersFileLocations
{
    /**
     * Register the default source directories for the given page classes.
     * Location string should be relative to the source root, which is
     * usually the root of the project.
     *
     * @example registerSourceDirectories([HydePage::class => '_pages'])
     *
     * @param  array  $directoryMapping{class:  string<HydePage>, location: string}
     */
    protected function registerSourceDirectories(array $directoryMapping): void
    {
        foreach ($directoryMapping as $class => $location) {
            /** @var HydePage $class */
            $class::$sourceDirectory = unslash(Hyde::getSourceRoot().'/'.unslash($location));
        }
    }

    /*
     * Register the optional output directories.
     * Some HTML pages, like Blade and Markdown pages are stored right in the _site/ directory.
     * However, some pages, like docs and posts are in subdirectories of the _site/ directory.
     * Location string should be relative to the root of the application.
     *
     * @example registerOutputDirectories([HydePage::class => 'docs'])
     *
     * @param  array  $directoryMapping{class: string<HydePage>, location: string}
     * @return void
     */
    protected function registerOutputDirectories(array $directoryMapping): void
    {
        foreach ($directoryMapping as $class => $location) {
            /** @var HydePage $class */
            $class::$outputDirectory = unslash($location);
        }
    }

    /**
     * If you are loading Blade views from a different directory,
     * you need to add the path to the view.php config. This is
     * here done automatically when registering the provider.
     */
    protected function discoverBladeViewsIn(string $directory): void
    {
        config(['view.paths' => array_unique(array_merge(
            config('view.paths', []),
            [base_path($directory)]
        ))]);
    }

    /**
     * The relative path to the directory when the compiled site is stored.
     *
     * Warning! This directory is emptied when compiling the site.
     */
    protected function storeCompiledSiteIn(string $directory): void
    {
        Site::$outputPath = $directory;
    }
}
