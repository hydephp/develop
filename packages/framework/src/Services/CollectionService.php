<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;

/**
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

        return array_map(function ($filepath) use ($model) {
            if (! str_starts_with(basename($filepath), '_')) {
                return basename($filepath, $model::getFileExtension());
            }
        }, glob(Hyde::path($model::qualifyBasename('*'))));
    }

    /**
     * Get all the Blade files in the resources/views/vendor/hyde/pages directory.
     *
     * @deprecated v0.44.x Will be renamed to getBladePageFiles
     * @return array
     */
    public static function getBladePageList(): array
    {
        return static::getSourceFileListForModel(BladePage::class);
    }

    /**
     * Get all the Markdown files in the _pages directory.
     *
     * @deprecated v0.44.x Will be renamed to getMarkdownPageFiles
     * @return array
     */
    public static function getMarkdownPageList(): array
    {
        return static::getSourceFileListForModel(MarkdownPage::class);
    }

    /**
     * Get all the Markdown files in the _posts directory.
     *
     * @deprecated v0.44.x Will be renamed to getMarkdownPostFiles
     * @return array
     */
    public static function getMarkdownPostList(): array
    {
        return static::getSourceFileListForModel(MarkdownPost::class);
    }

    /**
     * Get all the Markdown files in the _docs directory.
     *
     * @deprecated v0.44.x Will be renamed to getDocumentationPageFiles
     * @return array
     */
    public static function getDocumentationPageList(): array
    {
        return static::getSourceFileListForModel(DocumentationPage::class);
    }

    /**
     * Get all the Media asset file paths.
     * Returns a full file path, unlike the other get*List methods.
     */
    public static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path('_media/*.{'.str_replace(' ', '',
            config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
            ).'}'), GLOB_BRACE
        );
    }
}
