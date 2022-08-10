<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\FrontMatter\Schemas\DocumentationPageSchema;
use Hyde\Framework\Concerns\HasTableOfContents;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;
use Hyde\Framework\Models\Route;

class DocumentationPage extends AbstractMarkdownPage
{
    use DocumentationPageSchema;
    use HasTableOfContents;

    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs';
    public static string $template = 'hyde::layouts/docs';

    /** @inheritDoc */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        parent::__construct($identifier, $matter, $markdown);

        $this->constructDocumentationPageSchema();
    }

    /** @inheritDoc */
    public function getCurrentPagePath(): string
    {
        return trim(static::getOutputDirectory().'/'.basename($this->identifier), '/');
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
}
