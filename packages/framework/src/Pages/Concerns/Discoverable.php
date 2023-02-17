<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

/**
 * This trait implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 */
trait Discoverable
{
    protected static string $sourceDirectory;
    protected static string $outputDirectory;
    protected static string $fileExtension;

    /**
     * Get the directory in where source files are stored.
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
