<?php

declare(strict_types=1);

namespace Hyde\Markdown\Models;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Markdown\Contracts\MarkdownDocumentContract;
use Stringable;

/**
 * A MarkdownDocument is a simpler alternative to a MarkdownPage.
 *
 * It's an object that contains a parsed FrontMatter split from the body of the Markdown file.
 */
class MarkdownDocument implements MarkdownDocumentContract, Stringable
{
    use InteractsWithFrontMatter;

    public FrontMatter $matter;
    public Markdown $markdown;

    public function __construct(FrontMatter|array $matter = [], Markdown|string $body = '')
    {
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->markdown = $body instanceof Markdown ? $body : new Markdown($body);
    }

    public function __toString(): string
    {
        return $this->markdown->__toString();
    }

    public function markdown(): Markdown
    {
        return $this->markdown;
    }

    public static function parse(string $path): static
    {
        return MarkdownFileParser::parse($path);
    }
}
