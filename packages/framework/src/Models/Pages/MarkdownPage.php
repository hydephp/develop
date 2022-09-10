<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\AbstractMarkdownPage;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

class MarkdownPage extends AbstractMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
    public static string $fileExtension = '.md';
    public string $identifier;
    public Markdown $markdown;

    /** @interitDoc */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null)
    {
        $this->identifier = $identifier;
        $this->matter = $matter ?? new FrontMatter();
        $this->markdown = $markdown ?? new Markdown();

        HydePage::__construct($this->identifier, $this->matter);
    }

    /** @interitDoc */
    public static function make(string $identifier = '', array $matter = [], string $body = ''): static
    {
        return new static($identifier, new FrontMatter($matter), new Markdown($body));
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

    /** @inheritDoc */
    public function markdown(): Markdown
    {
        return $this->markdown;
    }
}
