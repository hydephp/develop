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

 * @see \Hyde\Framework\Testing\Feature\DiscoveryServiceTest
 */
class DiscoveryService
{
    /**
     * Supply a model::class constant and get a list of all the existing source file base names.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $model
     *
     * @throws \Hyde\Framework\Exceptions\UnsupportedPageTypeException
     *
     * @example Usage: DiscoveryService::getSourceFileListForModel(BladePage::class)
     * @example Returns: ['index', 'about', 'contact']
     */
    public static function getSourceFileListForModel(string $model): array
    {
        if (! class_exists($model) || ! is_subclass_of($model, HydePage::class)) {
            throw new UnsupportedPageTypeException($model);
        }

        return Hyde::files()->getSourceFiles($model)->flatten()->map(function (File $file) use ($model): string {
            return static::pathToIdentifier($model, $file->withoutDirectoryPrefix());
        })->toArray();
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
        return static::getSourceFileListForModel(BladePage::class);
    }

    public static function getMarkdownPageFiles(): array
    {
        return static::getSourceFileListForModel(MarkdownPage::class);
    }

    public static function getMarkdownPostFiles(): array
    {
        return static::getSourceFileListForModel(MarkdownPost::class);
    }

    public static function getDocumentationPageFiles(): array
    {
        return static::getSourceFileListForModel(DocumentationPage::class);
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

    /**
     * Create a filepath that can be opened in the browser from a terminal.
     */
    public static function createClickableFilepath(string $filepath): string
    {
        if (realpath($filepath) === false) {
            return $filepath;
        }

        return 'file://'.str_replace('\\', '/', realpath($filepath));
    }

    /**
     * Format a filename to a slug/identifier for a given model.
     *
     * @param class-string<\Hyde\Pages\Concerns\HydePage> $model
     * @param string $filename Example: index.blade.php
     * @return string Example: index
     */
    public static function pathToIdentifier(string $model, string $filename): string
    {
        $identifier = str_replace(Hyde::path($model::$sourceDirectory), '', $filename);

        if (str_ends_with($identifier, $model::$fileExtension)) {
            $identifier = substr($identifier, 0, -strlen($model::$fileExtension));
        }

        return unslash($identifier);
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf('_media/*.{%s}', str_replace(' ', '',
            (string) config('hyde.media_extensions', 'png,svg,jpg,jpeg,gif,ico,css,js')
        ));
    }
}
