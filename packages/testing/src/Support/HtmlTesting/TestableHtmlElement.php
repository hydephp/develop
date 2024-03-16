<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Illuminate\Support\Collection;

/**
 * A wrapper for an HTML element node, parsed into an assertable and queryable object.
 */
class TestableHtmlElement
{
    protected readonly string $html;
    protected readonly string $tag;
    protected readonly string $text;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The element's child nodes. */
    protected readonly Collection $nodes;

    public function __construct(string $html)
    {
        $this->html = $html;

        $this->tag = $this->parseTag($html);
        $this->text = $this->parseText($html);
    }

    protected function parseTag(string $html): string
    {
        preg_match('/^<([a-z0-9-]+)/i', $html, $matches);

        return $matches[1] ?? '';
    }

    protected function parseText(string $html): string
    {
        preg_match('/>([^<]+)</', $html, $matches);

        return trim(strip_tags($matches[1] ?? ''));
    }
}
