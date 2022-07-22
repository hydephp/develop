<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\StaticPageBuilder;

/**
 * Offloads file helper methods for the Hyde Facade.
 *
 * Provides a more fluent way of getting either the absolute path
 * to a model's source directory, or an absolute path to a file within it.
 *
 * These are intended to be used as a dynamic alternative to legacy code
 * Hyde::path('_pages/foo') becomes Hyde::getBladePagePath('foo')
 *
 * @see \Hyde\Framework\Hyde
 * @see \Hyde\Framework\Testing\Feature\FluentPathHelpersTest
 * @deprecated Should be merged into the main Hyde class.
 */
trait FluentPathHelpers
{
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
