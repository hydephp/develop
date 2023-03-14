<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Str;

use function class_basename;
use function array_unique;
use function array_merge;
use function base_path;
use function unslash;

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
     * @param  array<class-string<HydePage>, string>  $directoryMapping
     */
    protected function registerSourceDirectories(array $directoryMapping): void
    {
        /** @var class-string<HydePage> $class */
        foreach ($directoryMapping as $class => $location) {
            $class::setSourceDirectory(unslash(Hyde::getSourceRoot().'/'.unslash($location)));
        }
    }

    /**
     * Register the optional output directories.
     * Some HTML pages, like Blade and Markdown pages are stored right in the _site/ directory.
     * However, some pages, like docs and posts are in subdirectories of the _site/ directory.
     * Location string should be relative to the root of the application.
     *
     * @example registerOutputDirectories([HydePage::class => 'docs'])
     *
     * @param  array<class-string<HydePage>, string>  $directoryMapping
     */
    protected function registerOutputDirectories(array $directoryMapping): void
    {
        /** @var class-string<HydePage> $class */
        foreach ($directoryMapping as $class => $location) {
            $class::setOutputDirectory(unslash($location));
        }
    }

    /**
     * If you are loading Blade views from a different directory,
     * you need to add the path to the view.php config. This is
     * here done automatically when registering the provider.
     */
    protected function discoverBladeViewsIn(string $directory): void
    {
        Config::set(['view.paths' => array_unique(array_merge(
            Config::getArray('view.paths', []),
            [base_path($directory)]
        ))]);
    }

    /**
     * @param  string  $directory  The relative path to the directory when the compiled site is stored.
     *
     * Warning! This directory is emptied when compiling the site.
     */
    protected function storeCompiledSiteIn(string $directory): void
    {
        Hyde::setOutputDirectory($directory);
    }

    /**
     * @param  string  $directory  The relative path to the directory used for storing media files.
     */
    protected function useMediaDirectory(string $directory): void
    {
        Hyde::setMediaDirectory($directory);
    }

    protected function getSourceDirectoryConfiguration(string $class, string $default): string
    {
        return $this->getPageConfiguration('source_directories', $class, $default);
    }

    protected function getOutputDirectoryConfiguration(string $class, string $default): string
    {
        return $this->getPageConfiguration('output_directories', $class, $default);
    }

    private function getPageConfiguration(string $option, string $class, string $default): string
    {
        return Config::getNullableString("hyde.$option.".Str::kebab(class_basename($class))) /** @experimental Support for using kebab-case class names */
            ?? Config::getNullableString("hyde.$option.$class")
            ?? $default;
    }
}
