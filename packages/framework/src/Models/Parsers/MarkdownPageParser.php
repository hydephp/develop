<?php

namespace Hyde\Framework\Models\Parsers;

use Hyde\Framework\Contracts\AbstractPageParser;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Services\MarkdownFileService;

/**
 * Parses a Markdown file into a MarkdownPage object using the MarkdownPage intermediary.
 *
 * @todo Refactor to use dynamic path and extension resolvers
 */
class MarkdownPageParser extends AbstractPageParser
{
    protected string $pageModel = MarkdownPage::class;
    protected string $slug;

    /** @deprecated (handled in constructor) */
    public string $title = '';
    public string $body;

    public function execute(): void
    {
        $document = (new MarkdownFileService(
            Hyde::getMarkdownPagePath("/$this->slug.md")
        ))->get();

        $this->body = $document->body;
    }

    public function get(): MarkdownPage
    {
        return new MarkdownPage(
            matter: [],
            body: $this->body,
            title: $this->title,
            slug: $this->slug
        );
    }
}
