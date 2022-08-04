<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Facades\Markdown;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\MarkdownDocument;

/**
 * The base class for all Markdown-based Page Models.
 *
 * Normally, you would use the SourceFileParser to construct a MarkdownPage object.
 *
 * Extends the AbstractPage class to provide relevant
 * helpers for Markdown-based page model classes.
 * @see \Hyde\Framework\Models\Pages\MarkdownPage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 * @see \Hyde\Framework\Contracts\AbstractPage
 *
 * @see \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractMarkdownPage extends AbstractPage implements MarkdownDocumentContract
{
    public MarkdownDocument $markdown;

    public FrontMatter $matter;
    public string $body;
    public string $title;
    public string $identifier;

    public static string $fileExtension = '.md';

    public function __construct(string $identifier = '', FrontMatter|array $matter = [], string $body = '', ?string $title = null, ?MarkdownDocument $markdownDocument = null)
    {
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->body = $body;
        $this->title = $title ?? $this->matter('title', '');
        $this->identifier = $identifier;

        $this->markdown = $markdownDocument ?? new MarkdownDocument($this->matter, $body);
    }

    public function markdown(): MarkdownDocument
    {
        return $this->markdown;
    }

    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->matter->get($key, $default);
    }

    public function body(): string
    {
        return $this->markdown->body();
    }

    /** @inheritDoc */
    public function compile(): string
    {
        return view($this->getBladeView())->with([
            'title' => $this->title,
            'markdown' => Markdown::parse($this->body, static::class),
        ])->render();
    }
}
