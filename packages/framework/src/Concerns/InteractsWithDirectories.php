<?php

namespace Hyde\Framework\Concerns;

/**
 * @see \Hyde\Framework\Testing\Unit\InteractsWithDirectoriesConcernTest
 */
trait InteractsWithDirectories
{
    /**
     * Ensure the supplied directory exist by creating it if it does not.
     *
     * @param  string  $directory  absolute file path to the directory
     */
    public static function needsDirectory(string $directory): void
    {
        if (! file_exists($directory)) {
            mkdir($directory, recursive: true);
        }
    }

    /**
     * Ensure the supplied directories exist by creating them if they don't.
     *
     * @param  array  $directories  array with absolute file paths to the directories
     */
    public static function needsDirectories(array $directories): void
    {
        foreach ($directories as $directory) {
            static::needsDirectory($directory);
        }
    }

    /**
     * Ensure the supplied file's parent directory exists by creating it if it does not.
     *
     * @param  string  $file
     * @param  int  $levels
     * @return void
     */
    public static function needsParentDirectory(string $file, int $levels = 1): void
    {
        static::needsDirectory(dirname($file, $levels));
    }
}
