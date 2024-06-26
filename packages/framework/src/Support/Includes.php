<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Illuminate\Support\HtmlString;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\Blade;

use function trim;
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
     * @return string|null The raw contents of the partial file, or the default value if not found.
     */
    public static function get(string $filename, ?string $default = null): ?string
    {
        $path = static::path($filename);

        if (! Filesystem::exists($path)) {
            return $default;
        }

        return static::getFileContents($path);
    }

    /**
     * Get the HTML contents of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return HtmlString|null The contents of the partial file, or the default value if not found.
     */
    public static function html(string $filename, ?string $default = null): ?HtmlString
    {
        $path = static::normalizePath($filename, '.html');

        if (! Filesystem::exists($path)) {
            return $default === null ? null : static::renderHtml($default);
        }

        return static::renderHtml(static::getFileContents($path));
    }

    /**
     * Get the rendered Markdown of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return HtmlString|null The rendered contents of the partial file, or the default value if not found.
     */
    public static function markdown(string $filename, ?string $default = null): ?HtmlString
    {
        $path = static::normalizePath($filename, '.md');
        $contents = static::getFileContents($path);

        if ($contents === null) {
            return $default === null ? null : static::renderMarkdown($default);
        }

        return static::renderMarkdown($contents);
    }

    /**
     * Get the rendered Blade of a partial file in the includes directory.
     *
     * @param  string  $filename  The name of the partial file, with or without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return HtmlString|null The rendered contents of the partial file, or the default value if not found.
     */
    public static function blade(string $filename, ?string $default = null): ?HtmlString
    {
        $path = static::normalizePath($filename, '.blade.php');

        if (! Filesystem::exists($path)) {
            return $default === null ? null : static::renderBlade($default);
        }

        return static::renderBlade(static::getFileContents($path));
    }

    protected static function normalizePath(string $filename, string $extension): string
    {
        return static::path(basename($filename, $extension).$extension);
    }

    protected static function renderHtml(string $html): HtmlString
    {
        return new HtmlString($html);
    }

    protected static function renderMarkdown(string $markdown): HtmlString
    {
        return new HtmlString(trim(Markdown::render($markdown, MarkdownDocument::class)));
    }

    protected static function renderBlade(string $blade): HtmlString
    {
        return new HtmlString(Blade::render($blade));
    }

    protected static function getFileContents(string $path): ?string
    {
        if (! Filesystem::exists($path)) {
            return null;
        }

        return Filesystem::get($path);
    }
}
