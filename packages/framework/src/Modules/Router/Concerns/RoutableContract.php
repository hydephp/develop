<?php

namespace Hyde\Framework\Modules\Router\Concerns;

interface RoutableContract
{
    public static function getRouteSourcePath(): string;

    public static function getRouteOutputPath(): string;

    /** @see \Hyde\Framework\Contracts\PageContract::files() */
    public static function files(): array;

    /** Resolve a slug into a Hyde relative source file path with directory and extension */
    public static function qualifySourceFilePath(string $path): string;
}
