<?php

declare(strict_types=1);

namespace Hyde\Framework\Helpers;

use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\Blade;

class Includes
{
    /**
     * @var string The directory where includes are stored.
     */
    protected static string $includesDirectory = 'resources/_includes';

    /**
     * Return the path to the includes directory, or a partial within it, if requested.
     *
     * @param  string|null  $filename  The partial to return, or null to return the directory.
     * @return string Absolute Hyde::path() to the partial, or the includes directory.
     */
    public static function path(?string $filename = null): string
    {
        static::needsDirectory(static::$includesDirectory);

        return $filename === null
            ? Hyde::path(static::$includesDirectory)
            : Hyde::path(static::$includesDirectory.'/'.$filename);
    }

    /**
     * Get the raw contents of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, including the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
    public static function get(string $filename, ?string $default = null): ?string
    {
        $path = static::path($filename);

        if (! file_exists($path)) {
            return $default;
        }

        return file_get_contents($path);
    }

    /**
     * Get the rendered Markdown of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
    public static function markdown(string $filename, ?string $default = null): ?string
    {
        $path = static::path(basename($filename, '.md').'.md');

        if (! file_exists($path)) {
            return $default === null ? null : Markdown::render($default);
        }

        return Markdown::render(file_get_contents($path));
    }

    /**
     * Get the rendered Blade of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
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
