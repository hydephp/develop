<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Illuminate\Support\HtmlString;

/**
 * Vite facade for handling Vite-related operations.
 */
class Vite
{
    public static function running(): bool
    {
        return self::checkIfViteWasEnabledViaTheServeCommand() || Filesystem::exists('app/storage/framework/cache/vite.hot');
    }

    /** @param array<string> $paths */
    public static function assets(array $paths): HtmlString
    {
        $html = '<script src="http://localhost:5173/@vite/client" type="module"></script>';

        foreach ($paths as $path) {
            $html .= static::formatAssetPath($path);
        }

        return new HtmlString($html);
    }

    protected static function formatAssetPath(string $path): string
    {
        if (self::isCssPath($path)) {
            return static::formatStylesheetLink($path);
        }

        if (self::isJsPath($path)) {
            return static::formatScriptInclude($path);
        }

        return ''; // TODO: Throw an exception?
    }

    protected static function checkIfViteWasEnabledViaTheServeCommand(): bool
    {
        // TODO: Do we actually need this? Hotfile should be enough.
        return env('HYDE_SERVER_VITE') === 'enabled';
    }

    protected static function isCssPath(string $path): bool
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $path) === 1;
    }

    protected static function isJsPath(string $path): bool
    {
        return str_ends_with($path, '.js');
    }

    protected static function formatStylesheetLink(string $path): string
    {
        return sprintf('<link rel="stylesheet" href="http://localhost:5173/%s">', $path);
    }

    protected static function formatScriptInclude(string $path): string
    {
        return sprintf('<script src="http://localhost:5173/%s" type="module"></script>', $path);
    }
}
