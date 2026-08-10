<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Actions;

/**
 * Locate a static file to proxy.
 */
class AssetFileLocator
{
    public static function find(string $path): ?string
    {
        $path = trim($path, '/');

        $static = BASE_PATH.'/_static/'.$path;

        if (is_file($static)) {
            return $static;
        }

        // TODO: Custom media directories are unsupported because media is proxied before the application boots.
        if (str_starts_with($path, 'media/')) {
            $media = BASE_PATH.'/_media/'.substr($path, strlen('media/'));

            if (is_file($media)) {
                return $media;
            }
        }

        return null;
    }
}
