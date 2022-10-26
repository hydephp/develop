<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\MarkdownDocumentContract;
use Hyde\Framework\Models\Markdown\FrontMatter;
use Hyde\Framework\Models\Markdown\Markdown;
use Hyde\Hyde;

/**
 * The base class for all Markdown-based page models.
 *
 * @see \Hyde\Framework\Models\Pages\MarkdownPage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 * @see \Hyde\Framework\Concerns\HydePage
 * @see \Hyde\Framework\Testing\Feature\HydePageTest
 */
abstract class BaseMarkdownPage extends HydePage implements MarkdownDocumentContract
{
    public string $identifier;
    public Markdown $markdown;

    public static string $fileExtension = '.md';

    /**
     * Alternative to constructor, using primitive data types.
     * This method will construct then return a new instance of the class.
     *
     * @param  string  $identifier
     * @param  array  $matter
     * @param  string  $body
     * @return \Hyde\Framework\Concerns\BaseMarkdownPage
     */
    public static function make(string $identifier = '', array $matter = [], string $body = ''): static
    {
        return new static($identifier, new FrontMatter($matter), new Markdown($body));
    }

    /**
     * Construct a new MarkdownPage object from constructed data types.
     * Normally, this is done by the SourceFileParser.
     *
     * @param  string  $identifier
     * @param  \Hyde\Framework\Models\Markdown\FrontMatter|null  $matter
     * @param  \Hyde\Framework\Models\Markdown\Markdown|null  $markdown
     *
     * @see \Hyde\Framework\Actions\SourceFileParser
     *
     * @phpstan-consistent-constructor
     *
     * The types are strictly enforced to ensure a predictable behavior and constant access interface.
     */
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

    /**
     * Save the Markdown page object to disk by compiling the
     * front matter array to YAML and writing the body to the file.
     *
     * @return $this
     */
    public function save(): static
    {
        file_put_contents(Hyde::path($this->getSourcePath()), ltrim("$this->matter\n$this->markdown"));

        return $this;
    }
}
