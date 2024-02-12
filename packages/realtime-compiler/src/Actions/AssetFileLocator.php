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
        $strategies = [
            BASE_PATH.'/_site'.$path,
            BASE_PATH.'/_media'.$path,
            BASE_PATH.'/_site'.basename($path),
            BASE_PATH.'/_media/'.basename($path),
        ];

        foreach ($strategies as $strategy) {
            if (file_exists($strategy)) {
                return $strategy;
            }
        }

        return null;
    }
}
