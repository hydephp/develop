<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMXPath;
use Hyde\Hyde;
use DOMElement;
use DOMDocument;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;

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
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Document Dump</title><style>body { font-family: sans-serif; } .node { margin-left: 1em; }</style></head><body><h1>Document Dump</h1>';

        $html .= '<h2>Abstract Syntax Tree Node Inspection</h2>';
        $html .= "\n".'<div>'.$this->nodes->map(function (TestableHtmlElement $node): string {
            $data = Arr::except((array) $node, ['html']);

            return "\n".'  <ul class="node">'."\n".
                implode('', array_map(function (string|Collection $value, string $key): string {
                    if ($value instanceof Collection) {
                        return '      <li>'.$key.': <ul>'.implode('', $value->map(function (TestableHtmlElement $node): string {
                            return '<li><strong>'.ucfirst($node->tag).'</strong>: <span>'.$node->text.'</span></li>';
                        })->all()).'</ul></li>'."\n";
                    }

                    return '    <li><strong>'.ucfirst($key).'</strong>: <span>'.$value.'</span></li>'."\n";
                }, $data, array_keys($data)))
            .'  </ul>'."\n";
        })->implode('').'</div>'."\n";
        $html .= '</body></html>';

        return $html;
    }
}
