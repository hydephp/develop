<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMXPath;
use Hyde\Hyde;
use DOMElement;
use DOMDocument;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The document's element nodes. */
    public readonly Collection $nodes;

    public readonly int $level;

    public function __construct(string $html)
    {
        $this->level = 0;
        $this->nodes = $this->parseNodes($html);
    }

    protected function parseNodes(string $html): Collection
    {
        // This function parses the HTML into an abstract syntax tree (AST) of nodes, which can be inspected and queried.
        // It follows the same logical DOM structure, so <div><p>Text</p></div> would be represented as: div -> p

        $nodes = new Collection();

        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        $elements = $xpath->query('//*');

        foreach ($elements as $element) {
            // If it is a root element, add it to the nodes collection
            if ($element->parentNode instanceof DOMDocument) {
                $nodes->push(new TestableHtmlElement($element->ownerDocument->saveHTML($element), $this->level, $this, null));
            }

            // If it is a child element, add it to the last node in the nodes collection
            if ($element->parentNode instanceof DOMElement) {
                $nodes->last()->nodes->push(new TestableHtmlElement($element->ownerDocument->saveHTML($element), $this->level + 1, $this, $nodes->last()));
            }
        }

        return $nodes;
    }

    #[NoReturn]
    public function dd(bool $writeHtml = true, bool $dumpRawHtml = false): void
    {
        if ($writeHtml) {
            if ($dumpRawHtml) {
                $html = $this->html;
            } else {
                $timeStart = microtime(true);
                memory_get_usage(true);

                $html = $this->createAstInspectionDump();

                $timeEnd = number_format((microtime(true) - $timeStart) * 1000, 2);
                $memoryUsage = number_format(memory_get_usage(true) / 1024 / 1024, 2);

                $html .= sprintf("\n<footer><p><small>Generated in %s ms, using %s MB of memory.</small></p></footer>", $timeEnd, $memoryUsage);
            }
            file_put_contents(Hyde::path('document-dump.html'), $html);
        }
        dd($this->nodes);
    }

    protected function createAstInspectionDump(): string
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Document Dump</title><style>body { font-family: sans-serif; } .node { margin-left: 1em; }</style></head><body><h1>Document Dump</h1><h2>Abstract Syntax Tree Node Inspection</h2>';

        $html .= sprintf("\n<details open><summary><strong>Document</strong></summary>\n<ul>%s</ul></details>\n", $this->nodes->map(function (TestableHtmlElement $node): string {
            return $this->createDumpNodeMapEntry($node);
        })->implode(''));

        $html .= '</body></html>';

        return $html;
    }

    protected function createDumpListItem(string $key, string $value): string
    {
        return sprintf("      <li><strong>%s</strong>: <span>%s</span></li>\n", ucfirst($key), $value);
    }

    protected function createDumpNodeMapEntry(TestableHtmlElement $node): string
    {
        $data = $node->toArray();

        $list = sprintf("\n    <ul class=\"node\">\n%s  </ul>\n", implode('', array_map(function (string|Collection $value, string $key): string {
            if ($value instanceof Collection) {
                if ($value->isEmpty()) {
                    return sprintf("      <li><strong>%s</strong>: <span>None</span></li>\n", ucfirst($key));
                }

                return sprintf("      <li><strong>%s</strong>: <ul>%s</ul></li>\n", ucfirst($key), $value->map(function (TestableHtmlElement $node): string {
                    return $this->createDumpNodeMapEntry($node);
                })->implode(''));
            }

            return $this->createDumpListItem($key, $value);
        }, $data, array_keys($data))));

        if ($node->text) {
            if ($node->tag === 'style' && strlen($node->text) > 100) {
                $text = substr($node->text, 0, 100).'...';
            } else {
                $text = $node->text;
            }
            $title = sprintf('<%s>%s</%s>', $node->tag, $text, $node->tag);
        } else {
            $title = sprintf('<%s>', $node->tag);
        }

        return sprintf("  <li><%s><summary><strong>%s</strong></summary>%s  </details></li>\n", $node->level === 0 ? 'details open' : 'details', e($title), $list);
    }
}
