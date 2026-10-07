<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Support\Filesystem\MediaFile;

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

        if (static::isMediaPath($path)) {
            $relativePath = substr($path, strlen(static::mediaOutputDirectory()) + 1);
            $media = BASE_PATH.'/'.static::mediaDirectory().'/'.$relativePath;

            // Only serve media files that the build would publish, so the preview matches the built site.
            if (MediaFile::isPublishable($relativePath) && is_file($media)) {
                return $media;
            }
        }

        return null;
    }

    public static function isMediaPath(string $path): bool
    {
        return str_starts_with(trim($path, '/'), static::mediaOutputDirectory().'/');
    }

    /**
     * The serve command resolves the configured media directories and passes them to the server
     * process, as media is proxied before the application boots. The defaults apply when the
     * server is started directly, for example through the Herd integration.
     */
    protected static function mediaDirectory(): string
    {
        return getenv('HYDE_SERVER_MEDIA_DIRECTORY') ?: '_media';
    }

    protected static function mediaOutputDirectory(): string
    {
        return getenv('HYDE_SERVER_MEDIA_OUTPUT_DIRECTORY') ?: 'media';
    }
}
