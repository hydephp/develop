<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Hyde\Hyde;
use DOMElement;
use DOMDocument;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;
use Illuminate\Testing\Assert as PHPUnit;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    use HtmlTestingAssertions;

    public readonly string $html;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The document's element nodes. */
    public readonly Collection $nodes;

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->nodes = $this->parseNodes($html);
    }

    /**
     * Execute a testing callback on an element matching the given CSS selector.
     *
     * This is useful for fluent assertions while retaining the method chains of this class.
     */
    public function tapElement(string $selector, callable $callback): static
    {
        $element = $this->query($selector);

        if (! $element) {
            PHPUnit::fail("No element matching the selector '$selector' was found in the HTML.");
        }

        $callback($element);

        return $this;
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

    protected function parseNodes(string $html): Collection
    {
        $nodes = new Collection();
        $dom = new DOMDocument();

        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_NOXMLDECL | LIBXML_COMPACT | LIBXML_PARSEHUGE);

        // Initiate recursive parsing from the root element
        foreach ($dom->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $nodes->push($this->parseNodeRecursive($childNode));
            }
        }

        return $nodes;
    }

    protected function parseNodeRecursive(DOMElement $element, ?TestableHtmlElement $parent = null): TestableHtmlElement
    {
        // Initialize a new TestableHtmlElement for this DOMElement
        $htmlElement = new TestableHtmlElement($element->ownerDocument->saveHTML($element), $element, $this, $parent);

        // Iterate through child nodes and recursively parse them
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $htmlElement->nodes->push($this->parseNodeRecursive($childNode, $htmlElement));
            }
        }

        return $htmlElement;
    }

    protected function createAstInspectionDump(): string
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Document Dump</title><style>body { font-family: sans-serif; } .node { margin-left: 1em; }</style></head><body><h1>Document Dump</h1>';

        $html .= '<h2>Abstract Syntax Tree Node Inspection</h2>';
        $openAllButton = '<a href="javascript:void;" onclick="document.querySelectorAll(\'details\').forEach((el) => el.open = true);this.remove();">Open all</a>';
        $html .= sprintf("\n<details open><summary><strong>Document</strong> <small>$openAllButton</small></summary>\n<ul>%s</ul></details>\n", $this->nodes->map(function (TestableHtmlElement $node): string {
            return $this->createDumpNodeMapEntry($node);
        })->implode(''));

        $html .= '<section style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 1em;">'.
            sprintf('<div><h2>Document Preview</h2><iframe src="data:text/html;base64,%s" width="960px" height="600px"></iframe></div>', base64_encode($this->html)).
            sprintf('<div><h2>Raw HTML</h2><textarea cols="120" rows="30" readonly style="width: 960px; height: 600px; white-space: pre; font-family: monospace;">%s</textarea></div>', e($this->html)).
        '</section>';

        $html .= '</body></html>';

        return $html;
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

            return sprintf("      <li><strong>%s</strong>: <span>%s</span></li>\n", ucfirst($key), $value);
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

        return sprintf("  <li><%s><summary><strong>%s</strong></summary>%s  </details></li>\n", $node->tag === 'html' ? 'details open' : 'details', e($title), $list);
    }

    /**
     * Using CSS style selectors, this method allows for querying the document's nodes.
     *
     * @example $this->query('head > title')
     */
    protected function query(string $selector): ?TestableHtmlElement
    {
        $selectors = array_map('trim', explode('>', $selector));

        $nodes = $this->nodes;

        // While we have any selectors left, we continue to narrow down the nodes
        while ($selector = array_shift($selectors)) {
            $node = $nodes->first();

            if ($node === null) {
                return null;
            }

            $nodes = $this->queryCursorNode($selector, $node);
        }

        return $nodes->first();
    }

    protected function queryCursorNode(string $selector, TestableHtmlElement $node): Collection
    {
        // Scope the node's child nodes to the selector
        return $node->nodes->filter(fn (TestableHtmlElement $node) => $node->tag === $selector);
    }
}
