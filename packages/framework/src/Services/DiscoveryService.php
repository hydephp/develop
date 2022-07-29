<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Contracts\AbstractPage;
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
     * @param  string  $model  Class constant of the Model to get the Parser for.
     * @param  string  $slug  The slug of the source file to parse.
     *
     * @example getParserForModel(MarkdownPost::class, 'hello-world')
     *
     * @return object The constructed Parser instance.
     */
    public static function getParserInstanceForModel(string $model, string $slug): object
    {
        /** @var AbstractPage $model */
        return new $model::$parserClass($slug);
    }

    /**
     * Get the file extension for a models source files.
     */
    public static function getFileExtensionForModelFiles(string $model): string
    {
        /** @var AbstractPage $model */
        return $model::getFileExtension();
    }

    /**
     * Get the source directory path of a model.
     */
    public static function getFilePathForModelClassFiles(string $model): string
    {
        /** @var AbstractPage $model */
        return $model::getSourceDirectory();
    }

    /**
     * Determine the Page Model to use for a given file path.
     *
     * @deprecated v0.47.0-beta - Use the Router instead.
     *
     * @param  string  $filepath
     * @return string|false The model class constant, or false if none was found.
     *
     * @see \Hyde\Framework\Testing\Unit\DiscoveryServiceCanFindModelFromCustomSourceFilePathTest
     */
    public static function findModelFromFilePath(string $filepath): string|false
    {
        if (str_starts_with($filepath, MarkdownPost::getSourceDirectory())) {
            return MarkdownPost::class;
        }

        if (str_starts_with($filepath, DocumentationPage::getSourceDirectory())) {
            return DocumentationPage::class;
        }

        if (str_starts_with($filepath, MarkdownPage::getSourceDirectory())
            && str_ends_with($filepath, '.md')) {
            return MarkdownPage::class;
        }

        if (str_starts_with($filepath, BladePage::getSourceDirectory())
            && str_ends_with($filepath, '.blade.php')) {
            return BladePage::class;
        }

        return false;
    }

    /**
     * Create a filepath that can be opened in the browser from a terminal.
     *
     * @param  string  $filepath
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

    /**
     * Get all the Markdown files in the _docs directory.
     *
     * @return array
     */
    public static function getDocumentationPageFiles(): array
    {
        return self::getSourceFileListForModel(DocumentationPage::class);
    }

    /**
     * Supply a model::class constant and get a list of all the existing source file base names.
     *
     * @param  string  $model
     * @return array|false array on success, false if the class was not found
     *
     * @example DiscoveryService::getSourceFileListForModel(BladePage::class)
     */
    public static function getSourceFileListForModel(string $model): array|false
    {
        if (!class_exists($model) || !is_subclass_of($model, AbstractPage::class)) {
            return false;
        }

        // Scan the source directory, and directories therein, for files that match the model's file extension.

        $files = [];
        foreach (glob(Hyde::path($model::qualifyBasename('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (!str_starts_with(basename($filepath), '_')) {
                $files[] = self::formatSlugForModel($model, $filepath);
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
     * Get all the Markdown files in the _pages directory.
     *
     * @return array
     */
    public static function getMarkdownPageFiles(): array
    {
        return self::getSourceFileListForModel(MarkdownPage::class);
    }

    /**
     * Get all the Media asset file paths.
     * Returns a full file path, unlike the other get*List methods.
     */
    public static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path('_media/*.{' . str_replace(
                ' ',
                '',
                config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
            ) . '}'), GLOB_BRACE);
    }

    /**
     * Get all the Blade files in the _pages directory.
     *
     * @return array
     */
    public static function getBladePageFiles(): array
    {
        return self::getSourceFileListForModel(BladePage::class);
    }

    /**
     * Get all the Markdown files in the _posts directory.
     *
     * @return array
     */
    public static function getMarkdownPostFiles(): array
    {
        return self::getSourceFileListForModel(MarkdownPost::class);
    }
}
