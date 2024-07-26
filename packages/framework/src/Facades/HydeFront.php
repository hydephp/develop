<?php

declare(strict_types=1);

namespace Hyde\Facades;

use function sprintf;

class HydeFront
{
    /** @var string The default HydeFront SemVer tag to load. This constant is set to match the styles used for the installed framework version. */
    protected const HYDEFRONT_VERSION = 'v3.4';

    /** @var string The default HydeFront CDN path pattern. */
    protected const HYDEFRONT_CDN_URL = 'https://cdn.jsdelivr.net/npm/hydefront@%s/dist/%s';

    public static function version(): string
    {
        return static::HYDEFRONT_VERSION;
    }

    public static function cdnLink(string $file): string
    {
        return sprintf(static::HYDEFRONT_CDN_URL, static::version(), $file);
    }
}
