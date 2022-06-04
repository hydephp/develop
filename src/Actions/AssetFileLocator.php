<?php

namespace Hyde\RealtimeCompiler\Actions;

class AssetFileLocator
{
    public static function find(string $path): ?string
    {
        $path = BASE_PATH. '/_site'. $path;

        if (file_exists($path)) {
            return $path;
        }

        $path = BASE_PATH. '/_media'. $path;

        if (file_exists($path)) {
            return $path;
        }

        $path = BASE_PATH.'/'. basename($path);

        if (file_exists($path)) {
            return $path;
        }

        $path = BASE_PATH. '/_media/'. basename($path);

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }
}