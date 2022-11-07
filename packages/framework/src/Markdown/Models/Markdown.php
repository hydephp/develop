<?php

declare(strict_types=1);

namespace Hyde\Markdown\Models;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\MarkdownConverter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Stringable;

/**
 * A simple object representation of a Markdown file, with helpful methods to interact with it.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class Markdown implements Arrayable, Stringable, Htmlable
{
    protected string $body;

    /**
     * Create a new Markdown object from a string.
     */
    public function __construct(string $body = '')
    {
        $this->body = $body;
    }

    /**
     * Get the source Markdown body.
     */
    public function __toString(): string
    {
        return $this->body;
    }

    /**
     * Get the source Markdown body.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Compile the Markdown body to a string of HTML.
     *
     * If the Markdown being compiled is from a page model, supply
     * model's class name here so the dynamic parser can be used.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $sourceModel
     */
    public function compile(?string $sourceModel = null): string
    {
        return static::render($this->body, $sourceModel);
    }

    /**
     * Same as Markdown::compile(), but returns an HtmlString object.
     */
    public function toHtml(?string $sourceModel = null): HtmlString
    {
        return new HtmlString($this->compile($sourceModel));
    }

    /**
     * Get the Markdown document body as an array of lines.
     *
     * @return string[]
     */
    public function toArray(): array
    {
        return explode("\n", $this->body);
    }

    /**
     * Parse a Markdown file into a new Markdown object.
     */
    public static function fromFile(string $localFilepath): static
    {
        return MarkdownDocument::parse($localFilepath)->markdown();
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
            : (string) app(MarkdownConverter::class)->convert($markdown);
    }
}
