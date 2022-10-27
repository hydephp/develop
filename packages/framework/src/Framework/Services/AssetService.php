<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;

/**
 * Handles the retrieval of core asset files. Commonly used through the Asset facade.
 *
 * This class is loaded into the service container, making it easy to access and modify.
 *
 * @see \Hyde\Helpers\Asset
 */
class AssetService
{
    /**
     * The default HydeFront version to load.
     *
     * @property string $version HydeFront SemVer Tag
     */
    public string $version = 'v2.0';

    public function version(): string
    {
        return $this->version;
    }

    public function constructCdnPath(string $file): string
    {
        return 'https://cdn.jsdelivr.net/npm/hydefront@'.$this->version().'/dist/'.$file;
    }

    /**
     * Alias for constructCdnPath.
     */
    public function cdnLink(string $file): string
    {
        return $this->constructCdnPath($file);
    }

    public function mediaLink(string $file): string
    {
        return Hyde::relativeLink("media/$file").$this->getCacheBustKey($file);
    }

    public function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::path('_media').'/'.$file);
    }

    protected function getCacheBustKey(string $file): string
    {
        if (! config('hyde.cache_busting', true)) {
            return '';
        }

        return '?v='.md5_file(Hyde::path("_media/$file"));
    }
}
