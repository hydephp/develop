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
}
