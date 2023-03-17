<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Facades\Filesystem;
use Hyde\Markdown\Contracts\MarkdownDocumentContract;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\View;

use function dirname;
use function ltrim;
use function trim;

/**
 * The base class for all Markdown-based page models.
 *
 * @see \Hyde\Pages\MarkdownPage
 * @see \Hyde\Pages\MarkdownPost
 * @see \Hyde\Pages\DocumentationPage
 * @see \Hyde\Pages\Concerns\HydePage
 */
abstract class BaseMarkdownPage extends HydePage implements MarkdownDocumentContract
{
    public Markdown $markdown;

    public static string $fileExtension = '.md';

    /** @inheritDoc */
    public static function make(string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = ''): static
    {
        return new static($identifier, $matter, $markdown);
    }

    /** @inheritDoc */
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
        return View::make($this->getBladeView())->with([
            'title' => $this->title,
            'content' => $this->markdown->toHtml(static::class),
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
        Filesystem::ensureDirectoryExists(dirname($this->getSourcePath()));

        Filesystem::putContents($this->getSourcePath(), ltrim(trim("$this->matter\n$this->markdown")."\n"));

        return $this;
    }
}
