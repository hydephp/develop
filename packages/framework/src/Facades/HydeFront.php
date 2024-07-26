<?php

declare(strict_types=1);

namespace Hyde\Facades;

use function str_replace;

class HydeFront
{
    /** @var string The default HydeFront SemVer tag to load. This constant is set to match the styles used for the installed framework version. */
    protected const HYDEFRONT_VERSION = 'v3.4';

    /** @var string The default HydeFront CDN path pattern. The Blade-style placeholders are replaced with the proper values. */
    protected const HYDEFRONT_CDN_URL = 'https://cdn.jsdelivr.net/npm/hydefront@{{ $version }}/dist/{{ $file }}';

    public static function version(): string
    {
        return static::HYDEFRONT_VERSION;
    }

    public static function cdnLink(string $file): string
    {
        return str_replace(
            ['{{ $version }}', '{{ $file }}'], [static::version(), $file],
            static::HYDEFRONT_CDN_URL
        );
    }
}
