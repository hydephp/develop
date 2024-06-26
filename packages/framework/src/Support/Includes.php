<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\Blade;

use function basename;

/**
 * The Includes facade provides a simple way to access partials in the includes directory.
 *
 * Both Markdown and Blade includes will be rendered to HTML.
 */
class Includes
{
    /**
     * @var string The directory where includes are stored.
     */
    protected static string $includesDirectory = 'resources/includes';

    /**
     * Return the path to the includes directory, or a partial within it, if requested.
     *
     * @param  string|null  $filename  The partial to return, or null to return the directory.
     * @return string Absolute Hyde::path() to the partial, or the includes directory.
     */
    public static function path(?string $filename = null): string
    {
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

        if (! Filesystem::exists($path)) {
            return $default;
        }

        return Filesystem::getContents($path);
    }

    /**
     * Get the HTML contents of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The raw contents of the partial file, or the default value if not found.
     */
    public static function html(string $filename, ?string $default = null): ?string
    {
        $path = static::normalizePath($filename, '.html');

        if (! Filesystem::exists($path)) {
            return $default === null ? null : $default;
        }

        return Filesystem::getContents($path);
    }

    /**
     * Get the rendered Markdown of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The rendered contents of the partial file, or the default value if not found.
     */
    public static function markdown(string $filename, ?string $default = null): ?string
    {
        $path = static::normalizePath($filename, '.md');

        if (! Filesystem::exists($path)) {
            return $default === null ? null : Markdown::render($default);
        }

        return Markdown::render(Filesystem::getContents($path));
    }

    /**
     * Get the rendered Blade of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The rendered contents of the partial file, or the default value if not found.
     */
    public static function blade(string $filename, ?string $default = null): ?string
    {
        $path = static::normalizePath($filename, '.blade.php');

        if (! Filesystem::exists($path)) {
            return $default === null ? null : Blade::render($default);
        }

        return Blade::render(Filesystem::getContents($path));
    }

    protected static function normalizePath(string $filename, string $extension): string
    {
        return static::path(basename($filename, $extension).$extension);
    }
}
