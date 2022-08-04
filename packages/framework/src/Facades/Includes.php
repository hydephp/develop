<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\IncludeFacadeContract;
use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\Blade;

class Includes implements IncludeFacadeContract
{
    protected static string $includesDirectory = 'resources/_includes';

    public static function path(?string $filename = null): string
    {
        static::needsDirectory(static::$includesDirectory);

        return $filename === null
            ? Hyde::path(static::$includesDirectory)
            : Hyde::path(static::$includesDirectory.'/'.$filename);
    }

    /** @inheritDoc */
    public static function get(string $filename, ?string $default = null): ?string
    {
        $path = static::path($filename);

        if (! file_exists($path)) {
            return $default;
        }

        return file_get_contents($path);
    }

    /** @inheritDoc */
    public static function markdown(string $filename, ?string $default = null): ?string
    {
        $path = static::path(basename($filename, '.md').'.md');

        if (! file_exists($path)) {
            return $default === null ? null : Markdown::render($default);
        }

        return Markdown::render(file_get_contents($path));
    }

    /** @inheritDoc */
    public static function blade(string $filename, ?string $default = null): ?string
    {
        $path = static::path(basename($filename, '.blade.php').'.blade.php');

        if (! file_exists($path)) {
            return $default === null ? null : Blade::render($default);
        }

        return Blade::render(file_get_contents($path));
    }

    protected static function needsDirectory(string $directory): void
    {
        if (! file_exists($directory)) {
            mkdir($directory, recursive: true);
        }
    }
}
