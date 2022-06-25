<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\HasTableOfContents;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Parsers\DocumentationPageParser;

class DocumentationPage extends MarkdownDocument
{
    use HasTableOfContents;

    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs/';
    public static string $parserClass = DocumentationPageParser::class;

    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '')
    {
        parent::__construct($matter, $body, $title, $slug);

        $this->constructTableOfContents();
    }

    public function getCurrentPagePath(): string
    {
        return trim(Hyde::getDocumentationOutputDirectory().'/'.$this->slug, '/');
    }

    /** @internal */
    public function getOnlineSourcePath(): string|false
    {
        if (config('docs.source_file_location_base') === null) {
            return false;
        }

        return trim(config('docs.source_file_location_base'), '/').'/'.$this->slug.'.md';
    }

    /**
     * @deprecated deprecation candidate as it may conflict with static output directory.
     * @since v0.39.x (replaces `Hyde::docsDirectory()`)
     */
    public static function getDocumentationOutputPath(): string
    {
        return trim(config('docs.output_directory', 'docs'), '/\\');
    }
}
