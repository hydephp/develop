<?php
declare(strict_types=1);

namespace Hyde\Framework\Models\Markdown;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Framework\Contracts\MarkdownDocumentContract;

/**
 * A MarkdownDocument is a simpler alternative to a MarkdownPage.
 *
 * It's an object that contains a parsed FrontMatter split from the body of the Markdown file.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class MarkdownDocument implements MarkdownDocumentContract, \Stringable
{
    public FrontMatter $matter;
    public Markdown $markdown;

    public function __construct(FrontMatter|array $matter = [], Markdown|string $body = '')
    {
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->markdown = $body instanceof Markdown ? $body : new Markdown($body);
    }

    public function __toString(): string
    {
        return $this->markdown;
    }

    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $key ? $this->matter->get($key, $default) : $this->matter;
    }

    public function markdown(): Markdown
    {
        return $this->markdown;
    }

    public static function parse(string $localFilepath): static
    {
        return (new MarkdownFileParser($localFilepath))->get();
    }
}
