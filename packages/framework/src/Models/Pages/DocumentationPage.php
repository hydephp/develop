<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HasTableOfContents;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\Parsers\DocumentationPageParser;
use Hyde\Framework\Models\Route;

class DocumentationPage extends AbstractMarkdownPage
{
    use HasTableOfContents;

    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs';

    public static string $parserClass = DocumentationPageParser::class;

    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '')
    {
        parent::__construct($matter, $body, $title, $slug);
    }

    /** @internal */
    public function getOnlineSourcePath(): string|false
    {
        if (config('docs.source_file_location_base') === null) {
            return false;
        }

        return trim(config('docs.source_file_location_base'), '/').'/'.$this->slug.'.md';
    }

    public static function home(): ?RouteContract
    {
        return Route::getFromKey('docs/index');
    }
}
