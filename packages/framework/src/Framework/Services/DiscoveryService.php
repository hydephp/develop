<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;
use Illuminate\Support\Str;
use Hyde\Facades\Config;
use function glob;
use function implode;
use function sprintf;
use function unslash;

/**
 * General Discovery Helpers for HydePHP Auto-Discovery.
 *
 * Offloads FoundationCollection logic and provides helpers for common code.
 *
 * @see \Hyde\Framework\Testing\Feature\DiscoveryServiceTest
 */
class DiscoveryService
{
    final public const DEFAULT_MEDIA_EXTENSIONS = ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'];

    /**
     * Format a filename to an identifier for a given model. Unlike the basename function, any nested paths
     * within the source directory are retained in order to satisfy the page identifier definition.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @param  string  $filepath  Example: index.blade.php
     * @return string Example: index
     */
    public static function pathToIdentifier(string $pageClass, string $filepath): string
    {
        return unslash(Str::between(Hyde::pathToRelative($filepath),
            $pageClass::sourceDirectory().'/',
            $pageClass::fileExtension())
        );
    }

    /**
     * Get all the Media asset file paths.
     * Returns a full file path, unlike the other get*List methods.
     *
     * @return array<string> An array of absolute file paths.
     */
    public static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path(static::getMediaGlobPattern()), GLOB_BRACE) ?: [];
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf(Hyde::getMediaDirectory().'/{*,**/*,**/*/*}.{%s}', implode(',',
            Config::getArray('hyde.media_extensions', self::DEFAULT_MEDIA_EXTENSIONS)
        ));
    }
}
