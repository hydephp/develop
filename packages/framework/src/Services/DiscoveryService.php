<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\PageParserContract;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * The core service that powers all HydePHP file auto-discovery.
 *
 * Contains service methods to return helpful collections of arrays and lists,
 * and provides helper methods for source file auto-discovery used in the site
 * building process to determine where files are located and how to parse them.
 *
 * The CollectionService was in v0.53.0 merged into this class.
 */
class DiscoveryService
{
    public static function getParserClassForModel(string $model): string
    {
        /** @var AbstractPage $model */
        return $model::getParserClass();
    }

    /**
     * Create and get a constructed instance of a Model's Parser class.
     *
     * @param  string<AbstractPage>  $model  Class constant of the Model to get the Parser for.
     * @param  string  $slug  The slug of the source file to parse.
     *
     * @example getParserForModel(MarkdownPost::class, 'hello-world')
     *
     * @return PageParserContract The constructed Parser instance.
     */
    public static function getParserInstanceForModel(string $model, string $slug): PageParserContract
    {
        /** @var AbstractPage $model */
        return new $model::$parserClass($slug);
    }

    /**
     * Supply a model::class constant and get a list of all the existing source file base names.
     *
     * @param  string<AbstractPage>  $model
     * @return array
     *
     * @throws \Hyde\Framework\Exceptions\UnsupportedPageTypeException
     *
     * @example DiscoveryService::getSourceFileListForModel(BladePage::class)
     */
    public static function getSourceFileListForModel(string $model): array
    {
        if (! class_exists($model) || ! is_subclass_of($model, AbstractPage::class)) {
            throw new UnsupportedPageTypeException($model);
        }

        // Scan the source directory, and directories therein, for files that match the model's file extension.

        $files = [];
        foreach (glob(Hyde::path($model::qualifyBasename('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $files[] = self::formatSlugForModel($model, $filepath);
            }
        }

        return $files;
    }

    public static function getModelFileExtension(string $model): string
    {
        /** @var AbstractPage $model */
        return $model::getFileExtension();
    }

    public static function getModelSourceDirectory(string $model): string
    {
        /** @var AbstractPage $model */
        return $model::getSourceDirectory();
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
        return glob(Hyde::path('_media/*.{'.str_replace(
                ' ',
                '',
                config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
            ).'}'), GLOB_BRACE);
    }

    /**
     * Create a filepath that can be opened in the browser from a terminal.
     *
     * @param  string<AbstractPage>  $filepath
     * @return string
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
        /** @var AbstractPage $model */
        $slug = str_replace(Hyde::path($model::$sourceDirectory), '', $filepath);

        if (str_ends_with($slug, $model::$fileExtension)) {
            $slug = substr($slug, 0, -strlen($model::$fileExtension));
        }

        return unslash($slug);
    }
}
