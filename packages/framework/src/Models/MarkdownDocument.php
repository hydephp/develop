<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Facades\Markdown;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class MarkdownDocument implements MarkdownDocumentContract, Arrayable
{
    public FrontMatter $matter;
    public string $body;

    public function __construct(FrontMatter|array $matter = [], string $body = '')
    {
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->body = $body;
    }

    public function __toString(): string
    {
        return $this->body;
    }

    public function render(): string
    {
        return Markdown::parse($this->body);
    }

    public function matter(): FrontMatter
    {
        return $this->matter;
    }

    public function body(): string
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

    /**
     * @deprecated v0.56.0 - Will be renamed to parse()
     */
    public static function parseFile(string $localFilepath): static
    {
        return (new MarkdownFileParser(Hyde::path($localFilepath)))->get();
    }
}
