<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Facades\Markdown;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;
use Illuminate\Support\Arr;

/**
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class MarkdownDocument
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

    public static function parseFile(string $localFilepath): static
    {
        return (new MarkdownFileParser(Hyde::path($localFilepath)))->get();
    }
}
