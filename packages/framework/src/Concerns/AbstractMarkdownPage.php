<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\MarkdownDocumentContract;
use Hyde\Framework\Contracts\MarkdownPageContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

/**
 * The base class for all Markdown-based Page Models.
 *
 * @deprecated Will be merged into MarkdownPage
 *
 * Normally, you would use the SourceFileParser to construct a MarkdownPage object.
 *
 * Extends the HydePage class to provide relevant
 * helpers for Markdown-based page model classes.
 * @see \Hyde\Framework\Models\Pages\MarkdownPage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 * @see \Hyde\Framework\Concerns\HydePage
 * @see \Hyde\Framework\Testing\Feature\HydePageTest
 */
abstract class AbstractMarkdownPage extends HydePage implements MarkdownDocumentContract, MarkdownPageContract
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
        file_put_contents(Hyde::path($this->getSourcePath()), ltrim("$this->matter\n$this->markdown"));

        return $this;
    }
}
