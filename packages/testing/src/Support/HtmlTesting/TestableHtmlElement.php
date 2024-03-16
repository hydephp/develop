<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Illuminate\Support\Collection;

/**
 * A wrapper for an HTML element node, parsed into an assertable and queryable object.
 */
class TestableHtmlElement
{
    public readonly string $html;
    public readonly string $tag;
    public readonly string $text;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The element's child nodes. */
    public readonly Collection $nodes;

    public readonly int $level;

    public function __construct(string $html, int $level = 0)
    {
        $this->html = $html;
        $this->level = $level;

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
