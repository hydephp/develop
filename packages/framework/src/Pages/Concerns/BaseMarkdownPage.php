<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Facades\Filesystem;
use Hyde\Facades\Localization;
use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Markdown\Contracts\MarkdownDocumentContract;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\View;

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

    public static string $sourceExtension = '.md';

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

    /**
     * Create the page instance that the given language is rendered from, using the companion
     * source file for that language when the site has one, and this page's own otherwise.
     *
     * The variant is constructed rather than cloned, as its front matter is the front matter
     * of the localized source, and the page data derived from it, such as the page title,
     * is assigned once when the page is constructed, and cannot be reassigned after.
     */
    protected function localizedVariant(string $language): static
    {
        $path = Localization::sourcePath($this->getSourcePath(), $language);

        if ($path === null) {
            return parent::localizedVariant($language);
        }

        $document = MarkdownFileParser::parse($path);

        $page = new static($this->identifier, $document->matter, $document->markdown);

        $page->contentSourcePath = $path;

        return $page;
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
        Filesystem::ensureParentDirectoryExists($this->getSourcePath());

        Filesystem::putContents($this->getSourcePath(), ltrim(trim("$this->matter\n$this->markdown")."\n"));

        return $this;
    }
}
