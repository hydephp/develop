<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Illuminate\Support\Str;

use function rtrim;
use function explode;
use function implode;
use function md5_file;
use function file_exists;
use function str_replace;
use function preg_replace;
use function str_contains;
use function file_get_contents;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * This class provides static methods for interacting with versioned files,
 * as well as the HydeFront CDN service and the media directories.
 */
class Asset
{
    /** @var string The default HydeFront SemVer tag to load. This constant is set to match the styles used for the installed framework version. */
    final protected const HYDEFRONT_VERSION = 'v3.4';

    /** @var string The default HydeFront CDN path pattern. The Blade-style placeholders are replaced with the proper values. */
    final protected const HYDEFRONT_CDN_URL = 'https://cdn.jsdelivr.net/npm/hydefront@{{ $version }}/dist/{{ $file }}';

    public static function version(): string
    {
        return static::HYDEFRONT_VERSION;
    }

    public static function cdnLink(string $file): string
    {
        return static::constructCdnPath($file);
    }

    public static function mediaLink(string $file): string
    {
        return Hyde::mediaLink($file).static::getCacheBustKey($file);
    }

    public static function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::mediaPath($file));
    }

    public static function injectTailwindConfig(): string
    {
        if (! file_exists(Hyde::path('tailwind.config.js'))) {
            return '';
        }

        $config = Str::between(file_get_contents(Hyde::path('tailwind.config.js')), '{', '}');

        // Remove the plugins array, as it is not used in the frontend.
        if (str_contains($config, 'plugins: [')) {
            $tokens = explode('plugins: [', $config, 2);
            $tokens[1] = Str::after($tokens[1], ']');
            $config = implode('', $tokens);
        }

        return preg_replace('/\s+/', ' ', "/* tailwind.config.js */ \n".rtrim($config, ",\n\r"));
    }

    protected static function constructCdnPath(string $file): string
    {
        return str_replace(
            ['{{ $version }}', '{{ $file }}'], [static::version(), $file],
            static::HYDEFRONT_CDN_URL
        );
    }

    protected static function getCacheBustKey(string $file): string
    {
        return Config::getBool('hyde.enable_cache_busting', true)
            ? '?v='.md5_file(Hyde::mediaPath("$file"))
            : '';
    }
}
