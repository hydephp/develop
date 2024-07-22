<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Actions;

use Illuminate\Support\Str;

/**
 * Locate a static file to proxy.
 */
class AssetFileLocator
{
    public static function find(string $path): ?string
    {
        $path = trim($path, '/');

        $strategies = [
            BASE_PATH.'/_site/'.$path,
            BASE_PATH.'/_media/'.$path,
            BASE_PATH.'/_site/'.Str::after($path, 'media/'),
            BASE_PATH.'/_media/'.Str::after($path, 'media/'),
        ];

        foreach ($strategies as $strategy) {
            if (file_exists($strategy)) {
                return $strategy;
            }
        }

        return null;
    }
}
