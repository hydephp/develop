<?php

namespace Hyde\Framework\Modules\Router\Concerns;

interface RoutableContract
{
    /** Get the directory name of where the model source files are found, relative to Hyde root */
    public static function getRouteSourcePath(): string;

    /** Get the directory name of where compiled files should be placed, relative to _site */
    public static function getRouteOutputPath(): string;

    /**
     * Get the array list of source files for this model.
     * @see \Hyde\Framework\Contracts\PageContract::files()
     */
    public static function files(): array;

    /** Resolve a slug into a Hyde relative source file path with directory and extension */
    public static function qualifySourceFilePath(string $slug): string;
}
