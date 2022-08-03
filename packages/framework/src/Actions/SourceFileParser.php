<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
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

    protected string $slug;
    protected PageContract $page;

    public function __construct(string $pageClass, string $slug)
    {
        $this->validateExistence($pageClass, $slug);

        $this->slug = $slug;

        if ($pageClass === BladePage::class) {
            $this->page = $this->parseBladePage();
        } else {
            $this->page = $this->parseMarkdownPage($pageClass);
        }
    }

    protected function parseBladePage(): BladePage
    {
        return new BladePage($this->slug);
    }

    protected function parseMarkdownPage(string $pageClass): AbstractMarkdownPage
    {
        /** @var AbstractMarkdownPage $pageClass */
        $document = MarkdownFileParser::parse(
            $pageClass::qualifyBasename($this->slug)
        );

        $matter = $document->matter;
        $body = $document->body;

        return new $pageClass(
            matter: $matter,
            body: $body,
            title: FindsTitleForDocument::get($this->slug, $matter, $body),
            slug: $this->slug
        );
    }

    public function get(): PageContract
    {
        return $this->page;
    }
}
