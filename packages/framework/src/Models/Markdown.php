<?php

namespace Hyde\Framework\Models;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A simple object representation of a Markdown file, with helpful methods to interact with it.
 */
class Markdown
{
    public string $body;

    public function __construct(string $body = '')
    {
        $this->body = $body;
    }

    public static function fromFile(string $localFilepath): static
    {
        return MarkdownDocument::parseFile($localFilepath)->markdown;
    }
}