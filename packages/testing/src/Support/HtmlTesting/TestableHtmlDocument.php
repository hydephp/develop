<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMXPath;
use Hyde\Hyde;
use DOMElement;
use DOMDocument;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    public readonly string $html;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The document's element nodes. */
    public readonly Collection $nodes;

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

        return collect($xpath->query('//*'))->map(function (DOMElement $node): TestableHtmlElement {
            return new TestableHtmlElement($node->ownerDocument->saveHTML($node));
        });
    }

    #[NoReturn]
    public function dd(bool $writeHtml = true, bool $dumpRawHtml = false): void
    {
        if ($writeHtml) {
            if ($dumpRawHtml) {
                $html = $this->html;
            } else {
                $html = $this->createAstInspectionDump();
            }
            file_put_contents(Hyde::path('document-dump.html'), $html);
        }
        dd($this->nodes);
    }

    protected function createAstInspectionDump(): string
    {
        $dumper = new HtmlDumper();
        $cloner = new VarCloner();
        $data = $cloner->cloneVar($this);
        return $dumper->dump($data, true, ['maxDepth' => 5]);
    }
}
