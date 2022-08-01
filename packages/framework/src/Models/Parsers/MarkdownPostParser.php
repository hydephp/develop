<?php

namespace Hyde\Framework\Models\Parsers;

use Hyde\Framework\Contracts\AbstractPageParser;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;

class MarkdownPostParser extends AbstractPageParser
{
    protected string $pageModel = MarkdownPost::class;
    protected string $slug;

    /** @deprecated v0.44.x (handled in constructor) */
    public string $title = '';
    public string $body;
    public array $matter;

    public function execute(): void
    {
        $document = (new MarkdownFileParser(
            Hyde::getMarkdownPostPath("/$this->slug.md")
        ))->get();

        $this->matter = array_merge($document->matter, [
            'slug' => $this->slug,
        ]);

        $this->body = $document->body;
    }

    public function get(): MarkdownPost
    {
        return new MarkdownPost(
            matter: $this->matter,
            body: $this->body,
            title: $this->title,
            slug: $this->slug
        );
    }
}
