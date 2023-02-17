<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

/**
 * This trait implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 *
 * @deprecated Use base DiscoverablePage class instead.
 */
trait Discoverable
{
    /**
     * @var non-empty-string The directory in where source files are stored. Relative to the Hyde root directory.
     */
    protected static string $sourceDirectory;

    /**
     * @var string The output subdirectory to store compiled page HTML. Relative to the _site directory.
     */
    protected static string $outputDirectory;

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
     * Get the output subdirectory to store compiled HTML.
     */
    public static function outputDirectory(): string
    {
        return static::$outputDirectory;
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
     * Set the source directory for the HydePage class.
     */
    public static function setOutputDirectory(string $outputDirectory): void
    {
        static::$outputDirectory = unslash($outputDirectory);
    }

    /**
     * Set the file extension for the HydePage class.
     */
    public static function setFileExtension(string $fileExtension): void
    {
        static::$fileExtension = rtrim('.'.ltrim($fileExtension, '.'), '.');
    }
}
