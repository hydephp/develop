<?php

namespace Hyde\Framework\Models\Metadata;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\MetadataItemContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\RssFeedService;

class Metadata
{
    protected AbstractPage $page;

    public array $links = [];
    public array $metadata = [];
    public array $properties = [];
    public array $generics = [];

    public function __construct(AbstractPage $page)
    {
        $this->page = $page;
        $this->generate();
    }

    public function render(): string
    {
        return implode("\n", array_merge(
            $this->links,
            $this->metadata,
            $this->properties,
            $this->generics
        ));
    }

    public function add(MetadataItemContract|string $item): static
    {
        if ($item instanceof LinkItem) {
            $this->links[$item->uniqueKey()] = $item;
        } elseif ($item instanceof MetadataItem) {
            $this->metadata[$item->uniqueKey()] = $item;
        } elseif ($item instanceof OpenGraphItem) {
            $this->properties[$item->uniqueKey()] = $item;
        } else {
            $this->generics[] = $item;
        }

        return $this;
    }

    public function addIf($item, $condition): static
    {
        if ($condition) {
            $this->add($item);
        }

        return $this;
    }

    protected function generate(): void
    {
        foreach (config('hyde.meta', []) as $item) {
            $this->add($item);
        }

        $this->addIf(Meta::link('sitemap', Hyde::url('sitemap.xml'), [
            'type' => 'application/xml', 'title' => 'Sitemap',
        ]), Features::sitemap());

        $this->addIf(Meta::link('alternate', Hyde::url(RssFeedService::getDefaultOutputFilename()), [
            'type' => 'application/rss+xml', 'title' => RssFeedService::getDescription(),
        ]), Features::rss());

        $this->addIf(Meta::link('canonical', $this->page->canonicalUrl), !empty($this->page->canonicalUrl));

        if (! empty($this->page->title)) {
            $this->add(Meta::name('twitter:title', $this->page->htmlTitle()));
            $this->add(Meta::property('title', $this->page->htmlTitle()));
        }

        if ($this->page instanceof MarkdownPost) {
            foreach ($this->page->getPostMetadata() as $name => $content) {
                $this->add(Meta::name($name, $content));
            }
            foreach ($this->page->getMetaProperties() as $property => $content) {
                $this->add(Meta::property($property, $content));
            }
        }
    }
}
