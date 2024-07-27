<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Support\Filesystem\MediaFile;

use function implode;
use function collect;
use function sprintf;
use function glob;

/**
 * @internal Single-use trait for the Filesystem class.
 *
 * @see \Hyde\Foundation\Kernel\Filesystem
 */
trait HasMediaFiles
{
    /** @return array<string, \Hyde\Support\Filesystem\MediaFile> The array keys are the filenames relative to the _media/ directory */
    protected array $assets;

    /**
     * Get all media files in the project.
     *
     * @return array<string, \Hyde\Support\Filesystem\MediaFile>
     */
    public function assets(): array
    {
        return $this->assets ??= static::discoverMediaFiles();
    }

    protected static function discoverMediaFiles(): array
    {
        return collect(static::getMediaFiles())->mapWithKeys(function (string $path): array {
            $file = MediaFile::make($path);

            return [$file->getIdentifier() => $file];
        })->all();
    }

    protected static function getMediaFiles(): array
    {
        return glob(Hyde::path(static::getMediaGlobPattern()), GLOB_BRACE) ?: [];
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf(Hyde::getMediaDirectory().'/{*,**/*,**/*/*}.{%s}', implode(',',
            Config::getArray('hyde.media_extensions', MediaFile::EXTENSIONS)
        ));
    }
}
