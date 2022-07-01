<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\AbstractPage;

/**
 * @deprecated will be renamed to RegistersFileLocations or similar
 */
trait RegistersDefaultDirectories
{
    /**
     * Register the default source directories for the given page classes.
     * Location string should be relative to the root of the application.
     *
     * @example registerSourceDirectories([AbstractPage::class => '_pages'])
     *
     * @param  array  $directoryMapping{class: string<AbstractPage>, location: string}
     * @return void
     */
    protected function registerSourceDirectories(array $directoryMapping): void
    {
        foreach ($directoryMapping as $class => $location) {
            /** @var AbstractPage $class */
            $class::$sourceDirectory = $location;
        }
    }

    /*
     * Register the optional output directories.
     * Some HTML pages, like Blade and Markdown pages are stored right in the _site/ directory.
     * However, some pages, like docs and posts are in subdirectories of the _site/ directory.
     * Location string should be relative to the root of the application.
     *
     * @example registerOutputDirectories([AbstractPage::class => 'docs'])
     *
     * @param  array  $directoryMapping{class: string<AbstractPage>, location: string}
     * @return void
     */
    protected function registerOutputDirectories(array $directoryMapping): void
    {
        foreach ($directoryMapping as $class => $location) {
            /** @var AbstractPage $class */
            $class::$outputDirectory = $location;
        }
    }
}
