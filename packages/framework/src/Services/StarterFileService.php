<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;

/**
 * Manage the content files that should be included in Hyde/Hyde installations.
 *
 * @deprecated 0.20.x-dev This is managed by a CI action that keeps Hyde/Hyde up to date.
 */
class StarterFileService
{
    /**
     * Mapping of the source file to the destination file.
     * ['vendorPath' => 'hydePath'].
     */
    public static array $files = [
        'resources/views/homepages/welcome.blade.php' => '_pages/index.blade.php',
        'resources/views/pages/404.blade.php' => '_pages/404.blade.php',
    ];

    /**
     * Publish all the starter files.
     */
    public static function publish(): void
    {
        $files = static::$files;
        foreach ($files as $source => $file) {
            static::publishFile($source, $file);
        }
    }

    /**
     * Publish a single starter file.
     */
    protected static function publishFile(string $from, string $to): void
    {
        Hyde::copy(Hyde::vendorPath($from), Hyde::path($to));
    }
}
