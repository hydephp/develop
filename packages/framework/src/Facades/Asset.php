<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;

use function md5_file;
use function file_exists;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * This class provides static methods for interacting with versioned files,
 * as well as the HydeFront CDN service and the media directories.
 */
class Asset
{
    public static function mediaLink(string $file): string
    {
        return Hyde::mediaLink($file).static::getCacheBustKey($file);
    }

    public static function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::mediaPath($file));
    }

    protected static function getCacheBustKey(string $file): string
    {
        return Config::getBool('hyde.enable_cache_busting', true)
            ? '?v='.md5_file(Hyde::mediaPath("$file"))
            : '';
    }
}
