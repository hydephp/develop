<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

/**
 * The base class for all Markdown-based Page Models.
 *
 * Normally, you would use the SourceFileParser to construct a MarkdownPage object.
 *
 * Extends the AbstractPage class to provide relevant
 * helpers for Markdown-based page model classes.
 *
 * @see \Hyde\Framework\Models\Pages\MarkdownPage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 * @see \Hyde\Framework\Contracts\AbstractPage
 * @see \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractMarkdownPage extends AbstractPage implements MarkdownDocumentContract, MarkdownPageContract
{
    public string $identifier;
    public Markdown $markdown;

    public static string $fileExtension = '.md';

    /** @interitDoc */
    public static function make(string $identifier = '', array $matter = [], string $body = ''): static
    {
        return new static($identifier, new FrontMatter($matter), new Markdown($body));
    }

    /** @interitDoc */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        $this->identifier = $identifier;
        $this->matter = $matter ?? new FrontMatter();
        $this->markdown = $markdown ?? new Markdown();

        parent::__construct($this->identifier, $this->matter);
    }

    /** @inheritDoc */
    public function markdown(): Markdown
    {
        return $this->markdown;
    }

    /** @inheritDoc */
    public function compile(): string
    {
        return view($this->getBladeView())->with([
            'title' => $this->title,
            'markdown' => $this->markdown->compile(static::class),
        ])->render();
    }

    /** @inheritDoc */
    public function save(): static
    {
        // TODO: Implement save() method.

        return $this;
    }
}
