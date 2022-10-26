<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\BaseMarkdownPage;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Pages\BladePage;

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

        $this->page = $this->constructPage($pageClass);
    }

    protected function constructPage(string $pageClass): HydePage|BladePage|BaseMarkdownPage
    {
        if ($pageClass === BladePage::class) {
            return $this->parseBladePage();
        }

        if (is_subclass_of($pageClass, BaseMarkdownPage::class)) {
            return $this->parseMarkdownPage($pageClass);
        }

        return new $pageClass($this->identifier);
    }

    protected function parseBladePage(): BladePage
    {
        return new BladePage(
            $this->identifier,
            BladeMatterParser::parseFile(BladePage::sourcePath($this->identifier))
        );
    }

    protected function parseMarkdownPage(string $pageClass): BaseMarkdownPage
    {
        /** @var \Hyde\Framework\Concerns\BaseMarkdownPage $pageClass */
        $document = MarkdownFileParser::parse(
            $pageClass::sourcePath($this->identifier)
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
