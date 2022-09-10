<?php

namespace Hyde\Framework\Concerns\Internal;

/**
 * @internal This trait is not meant to be used outside of Hyde.
 *
 * Handles the filesystem logic for Hyde pages. All paths are relative to the project root.
 */
trait HandlesPageFilesystem
{
    /**
     * Get the directory in where source files are stored.
     */
    final public static function sourceDirectory(): string
    {
        return unslash(static::$sourceDirectory);
    }

    /**
     * Get the output subdirectory to store compiled HTML.
     */
    final public static function outputDirectory(): string
    {
        return unslash(static::$outputDirectory);
    }

    /**
     * Get the file extension of the source files.
     */
    final public static function fileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /**
     * Qualify a page identifier into a referenceable local file path.
     */
    public static function sourcePath(string $identifier): string
    {
        return static::sourceDirectory().'/'.unslash($identifier).static::fileExtension();
    }

    /**
     * Get the proper site output path for a page model.
     */
    public static function outputPath(string $identifier): string
    {
        return static::routeKey($identifier).'.html';
    }

    /**
     * Get the path to the source file, relative to the project root.
     * In other words, qualify the identifier of the page instance.
     */
    public function getSourcePath(): string
    {
        return static::sourcePath($this->identifier);
    }

    /**
     * Get the path where the compiled page instance will be saved.
     */
    public function getOutputPath(): string
    {
        return static::outputPath($this->identifier);
    }
}
