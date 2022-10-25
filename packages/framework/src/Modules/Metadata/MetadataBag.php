<?php

namespace Hyde\Framework\Modules\Metadata;

use Hyde\Framework\Concerns\HydePage;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Holds the metadata tags for a page or the site model.
 *
 * @see \Hyde\Framework\Testing\Feature\MetadataTest
 */
class MetadataBag implements Htmlable
{
    protected HydePage $page;

    public array $links = [];
    public array $metadata = [];
    public array $properties = [];
    public array $generics = [];

    public function __construct(?HydePage $page = null)
    {
        if ($page) {
            $this->page = $page;
            $this->generate();
        }
    }

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

    public function add(MetadataElementContract|string $item): static
    {
        if ($item instanceof Models\LinkElement) {
            $this->links[$item->uniqueKey()] = $item;
        } elseif ($item instanceof Models\MetadataElement) {
            $this->metadata[$item->uniqueKey()] = $item;
        } elseif ($item instanceof Models\OpenGraphElement) {
            $this->properties[$item->uniqueKey()] = $item;
        } else {
            $this->generics[] = $item;
        }

        return $this;
    }

    protected function generate(): void
    {
        // Run any code when the object is instantiated.
    }

    protected function getPrefixedArray(string $type): array
    {
        $array = [];
        foreach ($this->{$group} as $key => $value) {
            $array[$type.':'.$key] = $value;
        }

        return $array;
    }
}
