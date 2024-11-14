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
        // Check if Vite was enabled via the serve command
        if (env('HYDE_SERVER_VITE') === 'enabled') {
            return true;
        }

        // Check if Vite dev server is running by attempting to connect to it
        // Todo: Improve performance on Windows (takes less than 1ms on macOS, but around 100ms on Windows)
        set_error_handler(fn () => false); // Todo: This warning surpressor does not work on Windows
        $server = fsockopen('localhost', 5173, $errno, $errstr, 0.1);
        restore_error_handler();

        if ($server) {
            fclose($server);

            return true;
        }

        return false;
    }

    public static function assets(array $paths): HtmlString
    {
        $html = sprintf('<script src="http://localhost:5173/@vite/client" type="module"></script>');

        foreach ($paths as $path) {
            if (str_ends_with($path, '.css')) {
                $html .= sprintf('<link rel="stylesheet" href="http://localhost:5173/%s">', $path);
            }

            if (str_ends_with($path, '.js')) {
                $html .= sprintf('<script src="http://localhost:5173/%s" type="module"></script>', $path);
            }
        }

        return new HtmlString($html);
    }
}
