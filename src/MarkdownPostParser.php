<?php

namespace Hyde\Framework;

use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Services\MarkdownFileService;

class MarkdownPostParser extends AbstractPageParser
{
    protected string $pageModel = MarkdownPost::class;
    protected string $slug;

    public array $matter;
    public string $body;
    public string $title;

    public function execute(): void
    {
        $document = (new MarkdownFileService(
            Hyde::path("_posts/$this->slug.md")
        ))->get();

        $this->matter = array_merge($document->matter, [
            'slug' => $this->slug,
        ]);

        if (isset($document->matter['title'])) {
            $this->title = $document->matter['title'];
        } else {
            $this->title = Hyde::titleFromSlug($this->slug);
        }

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
