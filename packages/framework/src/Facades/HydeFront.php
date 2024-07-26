<?php

declare(strict_types=1);

namespace Hyde\Facades;

use function sprintf;

/**
 * HydeFront is the NPM package that bundles the default precompiled CSS and JavaScript assets for HydePHP.
 *
 * This facade makes it easy to access these assets from the HydeFront CDN, automatically getting the correct version.
 */
class HydeFront
{
    /** @var string The default HydeFront SemVer tag to load. This constant is set to match the styles used for the installed framework version. */
    protected const HYDEFRONT_VERSION = 'v3.4';

    /** @var string The default HydeFront CDN path pattern. */
    protected const HYDEFRONT_CDN_URL = 'https://cdn.jsdelivr.net/npm/hydefront@%s/dist/%s';

    /**
     * Get the current version of the HydeFront package.
     *
     * @return string {@see HYDEFRONT_VERSION}
     */
    public static function version(): string
    {
        return static::HYDEFRONT_VERSION;
    }

    /**
     * Get the CDN link for a specific file.
     *
     * @param  'app.css'|'hyde.css'|'hyde.css.map'  $file
     */
    public static function cdnLink(string $file): string
    {
        return sprintf(static::HYDEFRONT_CDN_URL, static::version(), $file);
    }
}
