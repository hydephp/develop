<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;
use Illuminate\Support\Str;

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

        $this->page = match ($pageClass) {
            BladePage::class => $this->parseBladePage(),
            MarkdownPage::class => $this->parseMarkdownPage(),
            MarkdownPost::class => $this->parseMarkdownPost(),
            DocumentationPage::class => $this->parseDocumentationPage(),
        };
    }

    protected function parseBladePage(): BladePage
    {
        return new BladePage($this->slug);
    }

    protected function parseMarkdownPage(): MarkdownPage
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPagePath($this->slug.MarkdownPage::$fileExtension)
        ))->get();

        $matter = $document->matter;
        $body = $document->body;

        return new MarkdownPage(
            matter: $matter,
            body: $body,
            title: FindsTitleForDocument::get($this->slug, $matter, $body),
            slug: $this->slug
        );
    }

    protected function parseMarkdownPost(): MarkdownPost
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPostPath($this->slug.MarkdownPost::$fileExtension)
        ))->get();

        $matter = $document->matter;
        $body = $document->body;

        return new MarkdownPost(
            matter: $matter,
            body: $body,
            title: FindsTitleForDocument::get($this->slug, $matter, $body),
            slug: $this->slug
        );
    }

    protected function parseDocumentationPage(): DocumentationPage
    {
        $document = (new MarkdownFileParser(
            Hyde::getDocumentationPagePath($this->slug.DocumentationPage::$fileExtension)
        ))->get();

        $matter = array_merge($document->matter, [
            'slug' => $this->slug,
        ]);

        $body = $document->body;

        return new DocumentationPage(
            matter: $matter,
            body: $body,
            title: FindsTitleForDocument::get($this->slug, $matter, $body),
            slug: basename($this->slug),
            category: $this->getDocumentationPageCategory($matter),
        );
    }

    protected function getDocumentationPageCategory(array $matter): ?string
    {
        if (str_contains($this->slug, '/')) {
            return Str::before($this->slug, '/');
        }

        return $matter['category'] ?? null;
    }

    public function get(): PageContract
    {
        return $this->page;
    }
}
