<?php

namespace Hyde\Framework\Contracts;

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
