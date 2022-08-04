<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\MarkdownDocumentContract;
use Hyde\Framework\Facades\Markdown as MarkdownFacade;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Markdown\MarkdownFileParser;

/**
 * A MarkdownDocument is a simpler alternative to a MarkdownPage.
 *
 * It's an object that contains a parsed FrontMatter split from the body of the Markdown file.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class MarkdownDocument implements MarkdownDocumentContract
{
    public FrontMatter $matter;
    public Markdown $markdown;

    /** @deprecated */
    public string $body;

    public function __construct(FrontMatter|array $matter = [], Markdown|string $body = '')
    {
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->markdown = $body instanceof Markdown ? $body : new Markdown($body);

        $this->body = $this->markdown->body;
    }

    public function __toString(): string
    {
        return $this->markdown;
    }

    public function render(): string
    {
        return MarkdownFacade::render($this->body);
    }

    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $key ? $this->matter->get($key, $default) : $this->matter;
    }

    public function markdown(): Markdown
    {
        return $this->markdown;
    }

    /** @deprecated  */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * @deprecated v0.56.0 - Will be renamed to parse()
     */
    public static function parseFile(string $localFilepath): static
    {
        return (new MarkdownFileParser(Hyde::path($localFilepath)))->get();
    }
}
