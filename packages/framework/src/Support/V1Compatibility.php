<?php

declare(strict_types=1);

namespace Hyde\Support;

use JetBrains\PhpStorm\Deprecated;
use Hyde\Support\Filesystem\MediaFile;

/**
 * @internal Hyde Facade support to aid in the v1 to v2 transition.
 *
 * @deprecated All code here is deprecated, and exists to help you transition.
 */
trait V1Compatibility
{
    /**
     * @deprecated Use MediaFile::sourcePath() instead.
     * @see \Hyde\Support\Filesystem\MediaFile::sourcePath()
     */
    #[Deprecated(reason: 'Use MediaFile::sourcePath() instead', replacement: '\Hyde\Support\Filesystem\MediaFile::sourcePath()')]
    public static function mediaPath(string $path = ''): string
    {
        return MediaFile::sourcePath($path);
    }

    /**
     * @deprecated Use MediaFile::outputPath() instead.
     * @see \Hyde\Support\Filesystem\MediaFile::outputPath()
     */
    #[Deprecated(reason: 'Use MediaFile::outputPath() instead', replacement: '\Hyde\Support\Filesystem\MediaFile::outputPath()')]
    public static function siteMediaPath(string $path = ''): string
    {
        return MediaFile::outputPath($path);
    }
}
