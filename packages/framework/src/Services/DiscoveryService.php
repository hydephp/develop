<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * The Discovery Service (previously called BuildService) provides
 * helper methods for source file autodiscovery used in the building
 * process to determine where files are located and how to parse them.
 *
 * @deprecated v0.48.0 as autodiscovery is now powered by the Router. The helpers here can be moved to FluentPathHelpers.php
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
}
