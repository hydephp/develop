<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Deprecated;

use function md5_file;
use function file_exists;
use function file_get_contents;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * This class provides static methods for interacting with versioned files,
 * as well as the HydeFront CDN service and the media directories.
 */
class Asset
{
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

    protected static function getCacheBustKey(string $file): string
    {
        return Config::getBool('hyde.enable_cache_busting', true)
            ? '?v='.md5_file(Hyde::mediaPath("$file"))
            : '';
    }

    /**
     * @deprecated Use HydeFront::version() instead.
     *
     * @codeCoverageIgnore Deprecated method.
     */
    #[Deprecated(reason: 'Use HydeFront::version() instead.', replacement: '\Hyde\Facades\HydeFront::version()')]
    public static function version(): string
    {
        return HydeFront::version();
    }

    /**
     * @deprecated Use HydeFront::cdnLink() instead.
     *
     * @codeCoverageIgnore Deprecated method.
     */
    #[Deprecated(reason: 'Use HydeFront::cdnLink() instead.', replacement: '\Hyde\Facades\HydeFront::cdnLink()')]
    public static function cdnLink(string $file): string
    {
        return HydeFront::cdnLink($file);
    }
}
