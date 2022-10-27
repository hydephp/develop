<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Actions\GeneratesSidebarTableOfContents;
use Hyde\Framework\Concerns\BaseMarkdownPage;
use Hyde\Framework\Contracts\FrontMatter\DocumentationPageSchema;
use Hyde\Support\Route;

/**
 * Page class for documentation pages.
 *
 * Documentation pages are stored in the _docs directory and using the .md extension.
 * The Markdown will be compiled to HTML using the documentation page layout to the _site/docs/ directory.
 *
 * @see https://hydephp.com/docs/master/documentation-pages
 */
class DocumentationPage extends BaseMarkdownPage implements DocumentationPageSchema
{
    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs';
    public static string $template = 'hyde::layouts/docs';

    /** @inheritDoc */
    public function getRouteKey(): string
    {
        return trim(static::outputDirectory().'/'.basename($this->identifier), '/');
    }

    /** @internal */
    public function getOnlineSourcePath(): string|false
    {
        if (config('docs.source_file_location_base') === null) {
            return false;
        }

        return trim(config('docs.source_file_location_base'), '/').'/'.$this->identifier.'.md';
    }

    public static function home(): ?Route
    {
        return Route::exists(static::$outputDirectory.'/index')
            ? Route::get(static::$outputDirectory.'/index')
            : null;
    }

    public static function hasTableOfContents(): bool
    {
        return config('docs.table_of_contents.enabled', true);
    }

    /**
     * Generate Table of Contents as HTML from a Markdown document body.
     */
    public function getTableOfContents(): string
    {
        return (new GeneratesSidebarTableOfContents($this->markdown))->execute();
    }

    /**
     * Return the output path for the identifier basename so nested pages are flattened.
     */
    public function getOutputPath(): string
    {
        return static::outputPath(basename($this->identifier));
    }
}
