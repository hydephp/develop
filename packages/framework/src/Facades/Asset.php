<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Support\Filesystem\MediaFile;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * This class provides static methods for interacting with versioned files,
 * as well as the HydeFront CDN service and the media directories.
 */
class Asset
{
    public static function get(string $file): MediaFile
    {
        return MediaFile::get($file);
    }

    public static function exists(string $file): bool
    {
        return Filesystem::exists(MediaFile::sourcePath($file));
    }
}
