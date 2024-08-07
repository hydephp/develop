<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata;

use Hyde\Framework\Features\Metadata\Elements\LinkElement;
use Hyde\Framework\Features\Metadata\Elements\MetadataElement;
use Hyde\Framework\Features\Metadata\Elements\OpenGraphElement;
use Illuminate\Contracts\Support\Htmlable;

use function array_merge;
use function implode;

/**
 * Holds the metadata tags for a page or the site model.
 *
 * @see \Hyde\Framework\Features\Metadata\PageMetadataBag
 * @see \Hyde\Framework\Features\Metadata\GlobalMetadataBag
 */
class MetadataBag implements Htmlable
{
    /** @var array<string, MetadataElementContract> */
    protected array $links = [];

    /** @var array<string, MetadataElementContract> */
    protected array $metadata = [];

    /** @var array<string, MetadataElementContract> */
    protected array $properties = [];

    /** @var array<string> */
    protected array $generics = [];

    public function toHtml(): string
    {
        return $this->render();
    }

    public function render(): string
    {
        return implode("\n", $this->get());
    }

    public function get(): array
    {
        return array_merge(
            $this->getPrefixedArray('links'),
            $this->getPrefixedArray('metadata'),
            $this->getPrefixedArray('properties'),
            $this->getPrefixedArray('generics')
        );
    }

    public function add(MetadataElementContract|string $element): static
    {
        match (true) {
            $element instanceof LinkElement => $this->links[$element->uniqueKey()] = $element,
            $element instanceof MetadataElement => $this->metadata[$element->uniqueKey()] = $element,
            $element instanceof OpenGraphElement => $this->properties[$element->uniqueKey()] = $element,
            default => $this->addGenericElement((string) $element),
        };

        return $this;
    }

    protected function addGenericElement(string $element): void
    {
        $this->generics[] = $element;
    }

    /** @return array<string, MetadataElementContract> */
    protected function getPrefixedArray(string $type): array
    {
        /** @var array<string, MetadataElementContract> $bag */
        $bag = $this->{$type};

        $array = [];

        foreach ($bag as $key => $element) {
            $array["$type:$key"] = $element;
        }

        return $array;
    }
}
