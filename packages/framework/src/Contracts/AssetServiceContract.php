<?php

namespace Hyde\Framework\Contracts;

/**
 * @deprecated v0.61.0-beta - Type hint the AssetService::class instead
 */
interface AssetServiceContract
{
    /**
     * The HydeFront version to load.
     */
    public function version(): string;

    /**
     * Construct a URI path for the CDN using the static dist version.
     */
    public function constructCdnPath(string $file): string;
}
