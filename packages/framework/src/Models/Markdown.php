<?php

namespace Hyde\Framework\Models;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A simple object representation of a Markdown file, with helpful methods to interact with it.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class Markdown implements Arrayable
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

    public function __toString(): string
    {
        return $this->body;
    }

    /**
     * Return the Markdown document body explored by line into an array.
     *
     * @return string[]
     */
    public function toArray(): array
    {
        return explode("\n", $this->body);
    }

    public function body(): string
    {
        return $this->body;
    }
}
