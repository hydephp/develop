<?php

namespace Hyde\Framework\Models\Parsers;

use Hyde\Framework\Contracts\AbstractPageParser;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Services\MarkdownFileParser;
use Illuminate\Support\Str;

/**
 * Parses a Markdown file in the configured `_docs` directory into a DocumentationPage object.
 *
 * If the file is in a subdirectory relative to the base source directory (default _docs),
 * the subdirectory name will be used as the page's category. This only works for one level,
 * and the resulting file will still be put in the root of the docs output directory.
 */
class DocumentationPageParser extends AbstractPageParser
{
    protected string $pageModel = DocumentationPage::class;
    protected string $slug;

    /** @deprecated v0.44.x (handled in constructor) */
    public string $title = '';
    public string $body;
    public array $matter;

    public function execute(): void
    {
        $document = (new MarkdownFileParser(
            Hyde::getDocumentationPagePath("/$this->slug.md")
        ))->get();

        $this->body = $document->body;
        $this->matter = $document->matter;
    }

    public function get(): DocumentationPage
    {
        return new DocumentationPage(
            matter: $this->matter,
            body: $this->body,
            title: $this->title,
            slug: basename($this->slug),
            category: $this->getCategory(),
            localPath: $this->slug
        );
    }

    public function getCategory(): ?string
    {
        if (str_contains($this->slug, '/')) {
            return Str::before($this->slug, '/');
        }

        return $this->matter['category'] ?? null;
    }
}
