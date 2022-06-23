<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\AssetServiceContract;
use Hyde\Framework\Hyde;

class AssetService implements AssetServiceContract
{
    /**
     * The default HydeFront version to load.
     *
     * @property string $version HydeFront SemVer Tag
     */
    public string $version = 'v1.11';

    public function version(): string
    {
        return $this->version;
    }

    public function stylePath(): string
    {
        return $this->constructCdnPath('hyde.css');
    }

    public function scriptPath(): string
    {
        return $this->constructCdnPath('hyde.js');
    }

    public function constructCdnPath(string $file): string
    {
        return 'https://cdn.jsdelivr.net/npm/hydefront@'.$this->version().'/dist/'.$file;
    }

    public function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::path('_media').'/'.$file);
    }

    public function cdnLink(string $file): string
    {
        return $this->constructCdnPath($file);
    }
}
