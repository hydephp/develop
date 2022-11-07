<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Hyde;
use Hyde\Markdown\Contracts\MarkdownDocumentContract;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;

/**
 * The base class for all Markdown-based page models.
 *
 * @see \Hyde\Pages\MarkdownPage
 * @see \Hyde\Pages\MarkdownPost
 * @see \Hyde\Pages\DocumentationPage
 * @see \Hyde\Pages\Concerns\HydePage
 * @see \Hyde\Framework\Testing\Feature\HydePageTest
 */
abstract class BaseMarkdownPage extends HydePage implements MarkdownDocumentContract
{
    public Markdown $markdown;

    public static string $fileExtension = '.md';

    public static function make(string $identifier = '', FrontMatter|array $matter = [], Markdown|string $body = ''): static
    {
        return new static($identifier, $matter, $body);
    }

    public function __construct(string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = '')
    {
        $this->markdown = $markdown instanceof Markdown ? $markdown : new Markdown($markdown);

        parent::__construct($identifier, $matter);
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
