<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

/**
 * Converts Markdown to plain text.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\ConvertsMarkdownToPlainTextTest
 */
class ConvertsMarkdownToPlainText
{
    protected string $markdown;

    public function __construct(string $markdown)
    {
        $this->markdown = $markdown;
    }
}
