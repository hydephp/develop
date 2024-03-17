<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMElement;
use DOMDocument;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Testing\Assert as PHPUnit;

use function trim;
use function substr;
use function explode;
use function array_map;
use function array_shift;
use function str_starts_with;

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
    protected DOMDocument $document;

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->nodes = $this->parseNodes($html);
    }

    public function getRootElement(): TestableHtmlElement
    {
        return $this->nodes->first();
    }

    public function element(string $element): ?TestableHtmlElement
    {
        return match (true) {
            str_starts_with($element, '#') => $this->getElementById(substr($element, 1)),
            str_contains($element, '>') => $this->query($element),
            default => throw new InvalidArgumentException("The selector syntax '$element' is not supported."),
        };
    }

    public function getElementById(string $id): ?TestableHtmlElement
    {
        return $this->nodes->first(fn (TestableHtmlElement $node) => $node->element->getAttribute('id') === $id);
    }

    /**
     * Select an element from the document using a CSS selector.
     *
     * Note that this means all subsequent assertions will be scoped to the selected element.
     * Use {@see self::tapElement()} to execute a callback on the selected element while retaining the method chains.
     */
    public function getElementUsingQuery(string $selector): TestableHtmlElement
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
     * Use {@see self::getElementUsingQuery()} to scope subsequent assertions to the selected element.
     */
    public function tapElement(string $selector, callable $callback): static
    {
        $callback($this->getElementUsingQuery($selector));

        return $this;
    }

    /** @note Use this sparingly, as you generally should not care about the exact HTML structure. */
    public function assertStructureLooksLike($expected): static
    {
        return $this->doAssert(fn () => PHPUnit::assertSame($expected, $this->getStructure(), 'The HTML structure does not look like expected.'));
    }

    /** A better alternative to assertStructureLooksLike, as it only cares about the visible text. */
    public function assertLooksLike($expected): static
    {
        return $this->doAssert(fn () => PHPUnit::assertSame($expected, $this->getTextRepresentation(), 'The HTML text does not look like expected.'));
    }

    /**
     * Using CSS style selectors, this method allows for querying the document's nodes.
     * Note that the first element in the DOM is skipped, so you don't need to start with `html` or `body`.
     * The first matching selector will be returned, or null if no match was found.
     *
     * @example $this->query('head > title')
     */
    public function query(string $selector): ?TestableHtmlElement
    {
        $selectors = array_map('trim', explode('>', trim($selector, '> ')));

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

        $this->document = $dom;

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
