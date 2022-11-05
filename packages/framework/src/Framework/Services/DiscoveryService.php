<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\File;

/**
 * The core service that powers all HydePHP file auto-discovery.
 *
 * Contains service methods to return helpful collections of arrays and lists,
 * and provides helper methods for source file auto-discovery used in the site
 * building process to determine where files are located and how to parse them.
 *
 * The CollectionService was in v0.53.0 merged into this class.
 *
 * @see \Hyde\Framework\Testing\Feature\DiscoveryServiceTest
 */
class DiscoveryService
{
    /**
     * Supply a model::class constant and get a list of all the existing source file base names.
     *
     * @param  string<\Hyde\Pages\Concerns\HydePage>  $model
     *
     * @throws \Hyde\Framework\Exceptions\UnsupportedPageTypeException
     *
     * @example DiscoveryService::getSourceFileListForModel(BladePage::class)
     */
    public static function getSourceFileListForModel(string $model): array
    {
        if (! class_exists($model) || ! is_subclass_of($model, HydePage::class)) {
            throw new UnsupportedPageTypeException($model);
        }

        $files = [];
        Hyde::files()->getSourceFiles($model)->each(function (File $file) use (&$files, $model) {
            $files[] = self::formatSlugForModel($model, $file->withoutDirectoryPrefix());
        });

        return $files;
    }

    public static function getModelFileExtension(string $model): string
    {
        /** @var \Hyde\Pages\Concerns\HydePage $model */
        return $model::fileExtension();
    }

    public static function getModelSourceDirectory(string $model): string
    {
        /** @var \Hyde\Pages\Concerns\HydePage $model */
        return $model::sourceDirectory();
    }

    public static function getBladePageFiles(): array
    {
        return self::getSourceFileListForModel(BladePage::class);
    }

    public static function getMarkdownPageFiles(): array
    {
        return self::getSourceFileListForModel(MarkdownPage::class);
    }

    public static function getMarkdownPostFiles(): array
    {
        return self::getSourceFileListForModel(MarkdownPost::class);
    }

    public static function getDocumentationPageFiles(): array
    {
        return self::getSourceFileListForModel(DocumentationPage::class);
    }

    /**
     * Get all the Media asset file paths.
     * Returns a full file path, unlike the other get*List methods.
     */
    public static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path(static::getMediaGlobPattern()), GLOB_BRACE) ?: [];
    }

    /**
     * Create a filepath that can be opened in the browser from a terminal.
     *
     * @param  string<\Hyde\Pages\Concerns\HydePage>  $filepath
     */
    public static function createClickableFilepath(string $filepath): string
    {
        if (realpath($filepath) === false) {
            return $filepath;
        }

        return 'file://'.str_replace(
            '\\',
            '/',
            realpath($filepath)
        );
    }

    public static function formatSlugForModel(string $model, string $filepath): string
    {
        /** @var HydePage $model */
        $slug = str_replace(Hyde::path($model::$sourceDirectory), '', $filepath);

        if (str_ends_with($slug, $model::$fileExtension)) {
            $slug = substr($slug, 0, -strlen($model::$fileExtension));
        }

        return unslash($slug);
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf('_media/*.{%s}', str_replace(' ', '',
            (string) config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
        ));
    }
}
