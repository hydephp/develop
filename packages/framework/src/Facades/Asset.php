<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Support\Filesystem\MediaFile;

use function hyde;
use function file_exists;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * This class provides static methods for interacting with versioned files,
 * as well as the HydeFront CDN service and the media directories.
 */
class Asset
{
    public static function get(string $file): string
    {
        return hyde()->asset($file);
    }

    public static function mediaLink(string $file): string
    {
        return hyde()->mediaLink($file).MediaFile::getCacheBustKey($file);
    }

    public static function hasMediaFile(string $file): bool
    {
        return file_exists(MediaFile::sourcePath($file));
    }
}
