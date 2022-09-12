<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Actions\GeneratesSidebarTableOfContents;
use Hyde\Framework\Concerns\BaseMarkdownPage;
use Hyde\Framework\Contracts\FrontMatter\DocumentationPageSchema;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;
use Hyde\Framework\Models\Route;

class DocumentationPage extends BaseMarkdownPage implements DocumentationPageSchema
{
    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs';
    public static string $template = 'hyde::layouts/docs';

    /** @inheritDoc */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        parent::__construct($identifier, $matter, $markdown);
    }

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

    public static function home(): ?RouteContract
    {
        return Route::exists(static::$outputDirectory.'/index') ? Route::get(static::$outputDirectory.'/index') : null;
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
