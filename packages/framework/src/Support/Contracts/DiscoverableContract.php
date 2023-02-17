<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

/**
 * Interface for discoverable HydePage classes.
 *
 * @property string $sourceDirectory (static)
 * @property string $outputDirectory (static)
 * @property string $fileExtension (static)
 */
interface DiscoverableContract
{
    /**
     * Get the source directory for the HydePage class.
     *
     * @return non-empty-string
     */
    public static function sourceDirectory(): string;

    /**
     * Get the output directory for the HydePage class.
     */
    public static function outputDirectory(): string;

    /**
     * Get the file extension for the HydePage class.
     */
    public static function fileExtension(): string;

    /**
     * Set the source directory for the HydePage class.
     *
     * @param  non-empty-string  $sourceDirectory
     */
    public static function setSourceDirectory(string $sourceDirectory): void;

    /**
     * Set the output directory for the HydePage class.
     */
    public static function setOutputDirectory(string $outputDirectory): void;

    /**
     * Set the file extension for the HydePage class.
     */
    public static function setFileExtension(string $fileExtension): void;
}
