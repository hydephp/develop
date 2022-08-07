<?php

namespace Hyde\Framework\Models\Metadata;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RssFeedService;

class Metadata
{
    protected AbstractPage $page;

    public array $links = [];
    public array $metadata = [];
    public array $properties = [];

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
            $this->properties
        ));
    }

    public function add($item): static
    {
        if ($item instanceof LinkItem) {
            $this->links[] = $item;
        } else {
            throw new \InvalidArgumentException('Invalid item type ' . get_class($item));
        }

        return $this;
    }

    protected function generate(): void
    {
        if (Features::sitemap()) {
            $this->add(Meta::link('sitemap', Hyde::url('sitemap.xml'), [
                'type' => 'application/xml', 'title' => 'Sitemap',
            ]));
        }

        if (Features::rss()) {
            $this->add(Meta::link('alternate', Hyde::url(RssFeedService::getDefaultOutputFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedService::getDescription(),
            ]));
        }
    }
}
