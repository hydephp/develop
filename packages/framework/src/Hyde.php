<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\Internal\FileHelpers;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * General facade for Hyde services.
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class Hyde
{
    use FileHelpers;
    use Macroable;

    protected static string $basePath;

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public static function getBasePath(): string
    {
        /** @deprecated Set path in constructor when instantiating the Singleton. */
        if (! isset(static::$basePath)) {
            static::$basePath = getcwd();
        }

        return static::$basePath;
    }

    /**
     * @deprecated Set path in constructor when instantiating the Singleton.
     */
    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    // HydeHelperFacade

    public static function features(): Features
    {
        return new Features;
    }

    public static function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    public static function makeTitle(string $slug): string
    {
        $alwaysLowercase = ['a', 'an', 'the', 'in', 'on', 'by', 'with', 'of', 'and', 'or', 'but'];

        return ucfirst(str_ireplace(
            $alwaysLowercase,
            $alwaysLowercase,
            Str::headline($slug)
        ));
    }

    /**
     * Fluent file helper methods.
     *
     * Provides a more fluent way of getting either the absolute path
     * to a model's source directory, or an absolute path to a file within it.
     *
     * These are intended to be used as a dynamic alternative to legacy code
     * Hyde::path('_pages/foo') becomes Hyde::getBladePagePath('foo')
     */

    public static function getModelSourcePath(string $model, string $path = ''): string
    {
        if (empty($path)) {
            return static::path(DiscoveryService::getFilePathForModelClassFiles($model));
        }

        $path = unslash($path);

        return static::path(DiscoveryService::getFilePathForModelClassFiles($model).DIRECTORY_SEPARATOR.$path);
    }

    public static function getBladePagePath(string $path = ''): string
    {
        return static::getModelSourcePath(BladePage::class, $path);
    }

    public static function getMarkdownPagePath(string $path = ''): string
    {
        return static::getModelSourcePath(MarkdownPage::class, $path);
    }

    public static function getMarkdownPostPath(string $path = ''): string
    {
        return static::getModelSourcePath(MarkdownPost::class, $path);
    }

    public static function getDocumentationPagePath(string $path = ''): string
    {
        return static::getModelSourcePath(DocumentationPage::class, $path);
    }

    /**
     * Get the absolute path to the compiled site directory, or a file within it.
     */
    public static function getSiteOutputPath(string $path = ''): string
    {
        if (empty($path)) {
            return StaticPageBuilder::$outputPath;
        }

        $path = unslash($path);

        return StaticPageBuilder::$outputPath.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Decode an absolute path created with a Hyde::path() helper into its relative counterpart.
     */
    public static function pathToRelative(string $path): string
    {
        return str_starts_with($path, static::path()) ? unslash(str_replace(
            static::path(),
            '',
            $path
        )) : $path;
    }
}
