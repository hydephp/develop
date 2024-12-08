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

    public static function assets(array $paths): HtmlString
    {
        $html = '<script src="http://localhost:5173/@vite/client" type="module"></script>';

        foreach ($paths as $path) {
            if (str_ends_with($path, '.css')) {
                $html .= static::formatStylesheetLink($path);
            }

            if (str_ends_with($path, '.js')) {
                $html .= static::formatScriptInclude($path);
            }
        }

        return new HtmlString($html);
    }

    protected static function checkIfViteWasEnabledViaTheServeCommand(): bool
    {
        return env('HYDE_SERVER_VITE') === 'enabled';
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
