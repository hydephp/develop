<?php

namespace Hyde\Framework\Modules\Metadata;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Pages\MarkdownPost;
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
        $this->addDynamicPageMetadata($this->page);
    }

    protected function addDynamicPageMetadata(HydePage $page): void
    {
        if ($page->has('canonicalUrl')) {
            $this->add(Meta::link('canonical', $page->get('canonicalUrl')));
        }

        if ($page->has('title')) {
            $this->add(Meta::name('twitter:title', $page->htmlTitle()));
            $this->add(Meta::property('title', $page->htmlTitle()));
        }

        if ($page instanceof MarkdownPost) {
            $this->addMetadataForMarkdownPost($page);
        }
    }

    protected function addMetadataForMarkdownPost(MarkdownPost $page): void
    {
        $this->addPostMetadataIfExists($page, 'description');
        $this->addPostMetadataIfExists($page, 'author');
        $this->addPostMetadataIfExists($page, 'category', 'keywords');
        $this->addPostMetadataIfExists($page, 'canonicalUrl', 'url');

        if ($page->has('canonicalUrl')) {
            $this->add(Meta::property('url', $page->get('canonicalUrl')));
        }

        if ($page->has('date')) {
            $this->add(Meta::property('og:article:published_time', $page->date->datetime));
        }

        if ($page->has('image')) {
            $this->add(Meta::property('image', $this->resolveImageLink($page->get('image'))));
        }

        $this->add(Meta::property('type', 'article'));
    }

    protected function addPostMetadataIfExists(MarkdownPost $page, string $property, ?string $name = null): void
    {
        if ($page->has($property)) {
            $this->add(Meta::name($name ?? $property, $page->get($property)));
        }
    }

    protected function getPrefixedArray(string $group): array
    {
        $array = [];
        foreach ($this->{$group} as $key => $value) {
            $array[$group.':'.$key] = $value;
        }

        return $array;
    }

    protected function resolveImageLink(string $image): string
    {
        // Since this is run before the page is rendered, we don't have the currentPage property
        // but since we know that this is for a blog post we know what the property will be
        // since Hyde does not currently support custom Blog post output directories.
        return str_starts_with($image, 'http') ? $image : "../$image";
    }
}
