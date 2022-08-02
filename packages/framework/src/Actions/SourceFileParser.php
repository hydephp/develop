<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Parsers\DocumentationPageParser;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;

/**
 * Parses a source file and returns a new page model instance for it.
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
        } elseif ($pageClass === MarkdownPage::class) {
            $this->page = $this->parseMarkdownPage();
        } elseif ($pageClass === MarkdownPost::class) {
            $this->page = $this->parseMarkdownPost();
        } elseif($pageClass === DocumentationPage::class) {
            $this->page = $this->parseDocumentationPage();
        } else {
            throw new \InvalidArgumentException("Invalid page class: $pageClass");
        }
    }

    protected function parseBladePage(): BladePage
    {
        return new BladePage($this->slug);
    }

    protected function parseMarkdownPage(): MarkdownPage
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPagePath("/$this->slug.md")
        ))->get();

        $matter = $document->matter;
        $body = $document->body;

        return new MarkdownPage(
            matter: $matter,
            body: $body,
            title: '@todo convert trait to action',
            slug: $this->slug
        );
    }

    protected function parseMarkdownPost(): MarkdownPost
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPostPath("/$this->slug.md")
        ))->get();

        $matter = array_merge($document->matter, [
            'slug' => $this->slug,
        ]);

        $body = $document->body;

        return new MarkdownPost(
            matter: $matter,
            body: $body,
            title: '@todo convert trait to action',
            slug: $this->slug
        );
    }

    protected function parseDocumentationPage(): DocumentationPage
    {
        return (new DocumentationPageParser($this->slug))->get();
    }

    public function get(): PageContract
    {
        return $this->page;
    }
}
