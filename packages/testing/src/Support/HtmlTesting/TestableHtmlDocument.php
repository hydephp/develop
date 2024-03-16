<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMXPath;
use DOMDocument;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    protected readonly string $html;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The document's element nodes. */
    protected Collection $nodes;

    public function __construct(string $html)
    {
        $this->html = $html;

        $this->nodes = $this->parseNodes($html);
    }

    protected function parseNodes(string $html): Collection
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        return collect($xpath->query('//*'))->map(fn ($node) => new TestableHtmlElement($node->ownerDocument->saveHTML($node)));
    }

    #[NoReturn]
    public function dd(): void
    {
        dd($this->nodes);
    }
}
