<?php

declare(strict_types=1);

namespace Hyde\Markdown\Models;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\MarkdownConverter;
use Hyde\Pages\Concerns\PageContents;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * A simple object representation of a Markdown file, with helpful methods to interact with it.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class Markdown extends PageContents implements Htmlable
{
    public string $body;

    /**
     * Compile the Markdown body to a string of HTML.
     *
     * If the Markdown being compiled is from a page model, supply
     * model's class name here so the dynamic parser can be used.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     */
    public function compile(?string $pageClass = null): string
    {
        return static::render($this->body, $pageClass);
    }

    /**
     * Same as Markdown::compile(), but returns an HtmlString object.
     */
    public function toHtml(?string $pageClass = null): HtmlString
    {
        return new HtmlString($this->compile($pageClass));
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
    public static function render(string $markdown, ?string $pageClass = null): string
    {
        return $pageClass !== null
            ? (new MarkdownService($markdown, $pageClass))->parse()
            : (string) app(MarkdownConverter::class)->convert($markdown);
    }
}
