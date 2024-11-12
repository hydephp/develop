<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Illuminate\Support\HtmlString;

/**
 * Vite facade for handling Vite-related operations.
 */
class Vite
{
    protected static bool $isRunning = false;
    protected static bool $hasChecked = false;

    public static function running(): bool
    {
        if (! static::$hasChecked) {
            static::checkViteStatus();
        }

        return static::$isRunning;
    }

    protected static function checkViteStatus(): void
    {
        static::$hasChecked = true;

        // Try to establish a connection to the Vite server
        $connection = @fsockopen('localhost', 3000, $errno, $errstr, 0.1);
        
        if ($connection) {
            static::$isRunning = true;
            fclose($connection);
        }
    }

    public static function assets(array $paths): HtmlString
    {
        $html = sprintf('<script src="http://localhost:3000/@vite/client" type="module"></script>');

        foreach ($paths as $path) {
            if (str_ends_with($path, '.css')) {
                $html .= sprintf('<link rel="stylesheet" href="http://localhost:3000/%s">', $path);
            }

            if (str_ends_with($path, '.js')) {
                $html .= sprintf('<script src="http://localhost:3000/%s" type="module"></script>', $path);
            }
        }

        return new HtmlString($html);
    }
}
