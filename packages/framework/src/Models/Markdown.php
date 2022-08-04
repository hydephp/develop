<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Facades\Markdown as MarkdownFacade;
use Hyde\Framework\Modules\Markdown\MarkdownConverter;
use Hyde\Framework\Services\MarkdownService;
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
        return MarkdownDocument::parseFile($localFilepath)->markdown();
    }

    public function compile(): string
    {
        return static::render($this->body);
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

    /**
     * Render a Markdown string into HTML.
     *
     * If a source model is provided, the Markdown will be converted using the dynamic MarkdownService,
     * otherwise, the pre-configured singleton from the service container will be used instead.
     *
     * @return string $html
     */
    public static function render(string $markdown, ?string $sourceModel = null): string
    {
        return $sourceModel !== null
            ? (new MarkdownService($markdown, $sourceModel))->parse()
            : app(MarkdownConverter::class)->convert($markdown);
    }
}
