<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\AbstractPage;

trait RegistersDefaultDirectories
{
    /**
     * Register the default directories.
     *
     * @param  array  $directoryMapping
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
