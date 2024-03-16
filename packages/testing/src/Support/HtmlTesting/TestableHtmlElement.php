<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Testing\Assert as PHPUnit;

/**
 * A wrapper for an HTML element node, parsed into an assertable and queryable object.
 */
class TestableHtmlElement implements Arrayable
{
    use HtmlTestingAssertions;

    public readonly string $html;
    public readonly string $tag;
    public readonly string $text;
    public readonly ?string $id;

    /** @var array<string> */
    public readonly array $classes;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> The element's child nodes. */
    public readonly Collection $nodes;

    public readonly DOMElement $element;

    protected ?TestableHtmlElement $parent = null;

    public function __construct(string $html, DOMElement $element, ?TestableHtmlElement $parent = null, ?Collection $nodes = null)
    {
        $this->html = $html;
        $this->element = $element;

        if ($parent) {
            $this->parent = $parent;
        }

        $this->nodes = $nodes ?? new Collection();

        $this->tag = $this->parseTag($html);
        $this->text = $this->parseText($html);
        $this->id = $element->getAttribute('id') ?: null;
        $this->classes = array_filter(explode(' ', $element->getAttribute('class')));
    }

    /** @return array{tag: string, text: string, nodes: \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement>, id: string} */
    public function toArray(): array
    {
        return [
            'tag' => $this->tag,
            'text' => $this->text,
            'nodes' => $this->nodes,
            'id' => $this->id,
        ];
    }

    public function hasClass(string $class): static
    {
        PHPUnit::assertContains($class, $this->classes, "The class '$class' was not found in the element.");

        return $this;
    }

    public function doesNotHaveClass(string $class): static
    {
        PHPUnit::assertNotContains($class, $this->classes, "The class '$class' was found in the element.");

        return $this;
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
