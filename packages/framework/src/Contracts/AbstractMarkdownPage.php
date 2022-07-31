<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Concerns\HasDynamicTitle;
use Hyde\Framework\Models\MarkdownDocument;

/**
 * The base class for all Markdown-based Page Models.
 *
 * @since 0.44.x replaces MarkdownDocument
 *
 * Extends the AbstractPage class to provide relevant
 * helpers for Markdown-based page model classes.
 * @see \Hyde\Framework\Models\Pages\MarkdownPage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 * @see \Hyde\Framework\Contracts\AbstractPage
 *
 * @test \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractMarkdownPage extends AbstractPage implements MarkdownPageContract
{
    use HasDynamicTitle;

    public MarkdownDocument $markdown;

    public array $matter;
    public string $body;
    public string $title;
    public string $slug;

    public static string $fileExtension = '.md';

    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '', ?MarkdownDocument $markdownDocument = null)
    {
        $this->matter = $matter;
        $this->body = $body;
        $this->title = $title;
        $this->slug = $slug;

        $this->markdown = $markdownDocument ?? new MarkdownDocument($matter, $body);

        $this->constructDynamicTitle();
    }

    public function markdown(): MarkdownDocument
    {
        return $this->markdown;
    }

    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->markdown->matter($key, $default);
    }

    /** @inheritDoc */
    public function compile(): string
    {
        return view($this->getBladeView())->with([
            'title' => $this->title,
            'markdown' => MarkdownConverter::parse($this->body, static::class),
        ])->render();
    }
}
