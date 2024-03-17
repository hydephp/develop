<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMElement;
use DOMDocument;
use Illuminate\Support\Collection;
use Illuminate\Testing\Assert as PHPUnit;

use function explode;
use function array_map;
use function array_shift;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    use HtmlTestingAssertions;
    use DumpsDocumentState;

    public readonly string $html;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The document's element nodes. */
    public readonly Collection $nodes;

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->nodes = $this->parseNodes($html);
    }

    public function getRootElement(): TestableHtmlElement
    {
        return $this->nodes->first();
    }

    public function getElementById(string $id): ?TestableHtmlElement
    {
        return $this->nodes->first(fn (TestableHtmlElement $node) => $node->element->getAttribute('id') === $id);
    }

    /**
     * Using CSS style selectors, this method allows for querying the document's nodes.
     *
     * @example $this->query('head > title')
     */
    public function query(string $selector): ?TestableHtmlElement
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

    /**
     * Select an element from the document using a CSS selector.
     *
     * Note that this means all subsequent assertions will be scoped to the selected element.
     * Use {@see self::tapElement()} to execute a callback on the selected element while retaining the method chains.
     */
    public function element(string $selector): TestableHtmlElement
    {
        $element = $this->query($selector);

        if (! $element) {
            PHPUnit::fail("No element matching the selector '$selector' was found in the HTML.");
        }

        return $element;
    }

    /**
     * Execute a testing callback on an element matching the given CSS selector.
     *
     * This is useful for fluent assertions while retaining the method chains of this class.
     * Use {@see self::element()} to scope subsequent assertions to the selected element.
     */
    public function tapElement(string $selector, callable $callback): static
    {
        $callback($this->element($selector));

        return $this;
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
        $htmlElement = new TestableHtmlElement($element->ownerDocument->saveHTML($element), $element, $parent);

        // Iterate through child nodes and recursively parse them
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $htmlElement->nodes->push($this->parseNodeRecursive($childNode, $htmlElement));
            }
        }

        return $htmlElement;
    }

    protected function queryCursorNode(string $selector, TestableHtmlElement $node): Collection
    {
        // Scope the node's child nodes to the selector
        return $node->nodes->filter(fn (TestableHtmlElement $node) => $node->tag === $selector);
    }
}
