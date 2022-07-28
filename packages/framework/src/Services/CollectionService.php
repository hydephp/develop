<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * The core service that powers all HydePHP file auto-discovery.
 * 
 * Contains service methods to return helpful collections of arrays and lists.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\CollectionServiceTest
 */
class CollectionService
{
    /**
     * Supply a model::class constant and get a list of all the existing source file base names.
     *
     * @param  string  $model
     * @return array|false array on success, false if the class was not found
     *
     * @example CollectionService::getSourceFileListForModel(BladePage::class)
     */
    public static function getSourceFileListForModel(string $model): array|false
    {
        if (! class_exists($model) || ! is_subclass_of($model, AbstractPage::class)) {
            return false;
        }

        // Scan the source directory, and directories therein, for files that match the model's file extension.
        
        $files = [];
        foreach (glob(Hyde::path($model::qualifyBasename('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $files[] = static::formatSlugForModel($model, $filepath);
            }
        }

        return $files;
    }

    public static function formatSlugForModel(string $model, string $filepath): string
    {
        /** @var AbstractPage $model */
        $slug = str_replace(Hyde::path($model::$sourceDirectory), '', $filepath);

        if (str_ends_with($slug, $model::$fileExtension)) {
            $slug = substr($slug, 0, -strlen($model::$fileExtension));
        }

        $slug = unslash($slug);

        return $slug;
    }

    /**
     * Get all the Blade files in the _pages directory.
     *
     * @return array
     */
    public static function getBladePageFiles(): array
    {
        return static::getSourceFileListForModel(BladePage::class);
    }

    /**
     * Get all the Markdown files in the _pages directory.
     *
     * @return array
     */
    public static function getMarkdownPageFiles(): array
    {
        return static::getSourceFileListForModel(MarkdownPage::class);
    }

    /**
     * Get all the Markdown files in the _posts directory.
     *
     * @return array
     */
    public static function getMarkdownPostFiles(): array
    {
        return static::getSourceFileListForModel(MarkdownPost::class);
    }

    /**
     * Get all the Markdown files in the _docs directory.
     *
     * @return array
     */
    public static function getDocumentationPageFiles(): array
    {
        return static::getSourceFileListForModel(DocumentationPage::class);
    }

    /**
     * Get all the Media asset file paths.
     * Returns a full file path, unlike the other get*List methods.
     */
    public static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path('_media/*.{'.str_replace(
            ' ',
            '',
            config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
        ).'}'), GLOB_BRACE);
    }
}
