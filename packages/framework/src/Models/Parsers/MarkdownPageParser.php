<?php

namespace Hyde\Framework\Models\Parsers;

use Hyde\Framework\Contracts\AbstractPageParser;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;

/**
 * Parses a Markdown file into a MarkdownPage object using the MarkdownPage intermediary.
 *
 * @deprecated v0.56.0-beta
 *
 * @todo Refactor to use dynamic path and extension resolvers
 */
class MarkdownPageParser extends AbstractPageParser
{
    protected string $pageModel = MarkdownPage::class;
    protected string $slug;

    /** @deprecated v0.44.x (handled in constructor) */
    public string $title = '';
    public array $matter;
    public string $body;

    public function execute(): void
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPagePath("/$this->slug.md")
        ))->get();

        $this->matter = $document->matter;
        $this->body = $document->body;
    }

    public function get(): MarkdownPage
    {
        return new MarkdownPage(
            matter: $this->matter,
            body: $this->body,
            title: $this->title,
            slug: $this->slug
        );
    }
}
