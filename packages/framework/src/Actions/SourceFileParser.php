<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\AbstractMarkdownPage;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;

/**
 * Parses a source file and returns a new page model instance for it.
 *
 * Page Parsers are responsible for parsing a source file into a Page object,
 * and may also conduct pre-processing and/or data validation/assembly.
 *
 * Note that the Page Parsers do not compile any HTML or Markdown.
 *
 * @see \Hyde\Framework\Testing\Feature\SourceFileParserTest
 */
class SourceFileParser
{
    use ValidatesExistence;

    protected string $identifier;
    protected HydePage $page;

    public function __construct(string $pageClass, string $identifier)
    {
        $this->validateExistence($pageClass, $identifier);
        $this->identifier = $identifier;

        $this->page = $pageClass === BladePage::class
            ? $this->parseBladePage()
            : $this->parseMarkdownPage($pageClass);
    }

    protected function parseBladePage(): BladePage
    {
        return new BladePage(
            $this->identifier,
            (BladeMatterParser::parseFile(BladePage::qualifyBasename($this->identifier)))
        );
    }

    protected function parseMarkdownPage(string $pageClass): AbstractMarkdownPage
    {
        /** @var \Hyde\Framework\Concerns\AbstractMarkdownPage $pageClass */
        $document = MarkdownFileParser::parse(
            $pageClass::qualifyBasename($this->identifier)
        );

        return new $pageClass(
            identifier: $this->identifier,
            matter: $document->matter,
            markdown: $document->markdown
        );
    }

    public function get(): HydePage
    {
        return $this->page;
    }
}
