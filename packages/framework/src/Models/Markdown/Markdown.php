<?php

declare(strict_types=1);

namespace Hyde\Framework\Models\Markdown;

use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Services\MarkdownService;
use Illuminate\Contracts\Support\Arrayable;
use Stringable;

/**
 * A simple object representation of a Markdown file, with helpful methods to interact with it.
 *
 * @see \Hyde\Framework\Testing\Unit\MarkdownDocumentTest
 */
class Markdown implements Arrayable, Stringable
{
    public string $body;

    public function __construct(string $body = '')
    {
        $this->body = $body;
    }

    public function __toString(): string
    {
        return $this->body;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function compile(?string $sourceModel = null): string
    {
        return static::render($this->body, $sourceModel);
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
