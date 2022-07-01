<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;
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
        if ($model == BladePage::class) {
            return self::getBladePageList();
        }

        if ($model == MarkdownPage::class) {
            return self::getMarkdownPageList();
        }

        if ($model == MarkdownPost::class) {
            return self::getMarkdownPostList();
        }

        if ($model == DocumentationPage::class) {
            return self::getDocumentationPageList();
        }

        return false;
    }

    /**
     * Get all the Blade files in the resources/views/vendor/hyde/pages directory.
     *
     * @return array
     */
    public static function getBladePageList(): array
    {
        $array = [];

        foreach (glob(Hyde::path(BladePage::qualifyBasename('*'))) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $array[] = basename($filepath, '.blade.php');
            }
        }

        return $array;
    }

    /**
     * Get all the Markdown files in the _pages directory.
     *
     * @return array
     */
    public static function getMarkdownPageList(): array
    {
        $array = [];

        foreach (glob(Hyde::path(MarkdownPage::qualifyBasename('*'))) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $array[] = basename($filepath, '.md');
            }
        }

        return $array;
    }

    /**
     * Get all the Markdown files in the _posts directory.
     *
     * @return array
     */
    public static function getMarkdownPostList(): array
    {
        $array = [];

        foreach (glob(Hyde::path(MarkdownPost::qualifyBasename('*'))) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $array[] = basename($filepath, '.md');
            }
        }

        return $array;
    }

    /**
     * Get all the Markdown files in the _docs directory.
     *
     * @return array
     */
    public static function getDocumentationPageList(): array
    {
        $array = [];

        foreach (glob(Hyde::path(DocumentationPage::qualifyBasename('*'))) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $array[] = basename($filepath, '.md');
            }
        }

        return $array;
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
