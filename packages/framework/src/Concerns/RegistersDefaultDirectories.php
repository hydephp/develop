<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\AbstractPage;

/**
 * @deprecated will be renamed to RegistersFileLocations or similar
 */
trait RegistersDefaultDirectories
{
    /**
     * Register the default directories.
     *
     * @param  array  $directoryMapping
     * @return void
     */
    protected function registerDefaultDirectories(array $directoryMapping): void
    {
        foreach ($directoryMapping as $class => $location) {
            /** @var AbstractPage $class */
            $class::$sourceDirectory = $location;
        }
    }
}
