<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Concerns\HasDynamicTitle;
use Hyde\Framework\Models\MarkdownDocument;

/**
 * The base class for all Markdown-based Page Models.
 *
 * @since 0.44.x replaces MarkdownDocument
 *
 * Extends the AbstractPage class to provide relevant
 * helpers for Markdown-based page model classes.
 * @see \Hyde\Framework\Models\MarkdownPage
 * @see \Hyde\Framework\Models\MarkdownPost
 * @see \Hyde\Framework\Models\DocumentationPage
 */
abstract class AbstractMarkdownPage extends AbstractPage
{
    use HasDynamicTitle;

    public MarkdownDocument $markdown;

    public array $matter;
    public string $body;
    public string $title;
    public string $slug;

    public static string $fileExtension = '.md';

    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '', ?MarkdownDocument $markdown = null)
    {
        $this->matter = $matter;
        $this->body = $body;
        $this->title = $title;
        $this->slug = $slug;

        if ($markdown) {
            $this->markdown = $markdown;
        }

        $this->constructDynamicTitle();
    }
}
