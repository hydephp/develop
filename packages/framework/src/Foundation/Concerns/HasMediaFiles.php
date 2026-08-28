<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Support\Filesystem\MediaFile;
use Illuminate\Support\Collection;

use function collect;
use function basename;
use function in_array;
use function strtolower;

/**
 * @internal Single-use trait for the Filesystem class.
 *
 * @see \Hyde\Foundation\Kernel\Filesystem
 */
trait HasMediaFiles
{
    private const IGNORED_FILENAMES = ['thumbs.db', 'desktop.ini'];

    /** @var Collection<string, \Hyde\Support\Filesystem\MediaFile> The Collection keys are the filenames relative to the _media/ directory */
    protected Collection $assets;

    /**
     * Get all media files in the project.
     *
     * @return Collection<string, \Hyde\Support\Filesystem\MediaFile>
     */
    public function assets(): Collection
    {
        return $this->assets ??= static::discoverMediaFiles();
    }

    protected static function discoverMediaFiles(): Collection
    {
        return collect(static::getMediaFiles())->mapWithKeys(function (string $path): array {
            $file = MediaFile::make($path);

            return [$file->getIdentifier() => $file];
        });
    }

    protected static function getMediaFiles(): array
    {
        return Filesystem::findFiles(Hyde::getMediaDirectory(), false, recursive: true)
            ->reject(fn (string $path): bool => in_array(strtolower(basename($path)), self::IGNORED_FILENAMES, true))
            ->values()->all();
    }
}
