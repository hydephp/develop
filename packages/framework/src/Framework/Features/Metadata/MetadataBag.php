<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata;

use Hyde\Framework\Features\Metadata\Elements\LinkElement;
use Hyde\Framework\Features\Metadata\Elements\MetadataElement;
use Hyde\Framework\Features\Metadata\Elements\OpenGraphElement;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Holds the metadata tags for a page or the site model.
 *
 * @todo These properties should probably not be public
 *
 * @see \Hyde\Framework\Testing\Feature\MetadataTest
 * @see \Hyde\Framework\Features\Metadata\PageMetadataBag
 * @see \Hyde\Framework\Features\Metadata\GlobalMetadataBag
 */
class MetadataBag implements Htmlable
{
    public array $links = [];
    public array $metadata = [];
    public array $properties = [];
    public array $generics = [];

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
        if ($element instanceof LinkElement) {
            return $this->addElement('links', $element);
        }

        if ($element instanceof MetadataElement) {
            return $this->addElement('metadata', $element);
        }

        if ($element instanceof OpenGraphElement) {
            return $this->addElement('properties', $element);
        }

        return $this->addGenericElement($element);
    }

    protected function addElement(string $type, MetadataElementContract $element): MetadataBag
    {
        ($this->$type)[$element->uniqueKey()] = $element;

        return $this;
    }

    protected function addGenericElement(string $element): MetadataBag
    {
        $this->generics[] = $element;

        return $this;
    }

    protected function getPrefixedArray(string $type): array
    {
        $array = [];

        foreach ($this->{$type} as $key => $value) {
            $array["$type:$key"] = $value;
        }

        return $array;
    }
}
