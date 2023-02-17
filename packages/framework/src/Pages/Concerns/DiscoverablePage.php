<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Support\Contracts\DiscoverableContract;

/**
 * @deprecated This class implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 */
abstract class DiscoverablePage extends HydePage implements DiscoverableContract
{
    /**
     * @var non-empty-string The directory in where source files are stored. Relative to the Hyde root directory.
     */
    protected static string $sourceDirectory;

    /**
     * @var string The file extension of the source files. Normalized to include a leading dot.
     */
    protected static string $fileExtension;

    /**
     * Get the directory in where source files are stored.
     *
     * @return non-empty-string
     */
    public static function sourceDirectory(): string
    {
        return static::$sourceDirectory;
    }

    /**
     * Get the file extension of the source files.
     */
    public static function fileExtension(): string
    {
        return static::$fileExtension;
    }

    /**
     * Set the output directory for the HydePage class.
     *
     * @param  non-empty-string  $sourceDirectory
     */
    public static function setSourceDirectory(string $sourceDirectory): void
    {
        static::$sourceDirectory = unslash($sourceDirectory);
    }

    /**
     * Set the file extension for the HydePage class.
     */
    public static function setFileExtension(string $fileExtension): void
    {
        static::$fileExtension = rtrim('.'.ltrim($fileExtension, '.'), '.');
    }
}
