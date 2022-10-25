<?php

namespace Hyde\Framework\Modules\Metadata\Models;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Metadata\MetadataBag;
use Hyde\Framework\Services\RssFeedService;
use Illuminate\Support\Facades\View;

/**
 * @see \Hyde\Framework\Testing\Feature\GlobalMetadataBagTest
 */
class GlobalMetadataBag extends MetadataBag
{
    public static function make(): static
    {
        $metadataBag = new self();

        foreach (config('hyde.meta', []) as $item) {
            $metadataBag->add($item);
        }

        if (Features::sitemap()) {
            $metadataBag->add(Meta::link('sitemap', Hyde::url('sitemap.xml'), [
                'type' => 'application/xml', 'title' => 'Sitemap',
            ]));
        }

        if (Features::rss()) {
            $metadataBag->add(Meta::link('alternate', Hyde::url(RssFeedService::getDefaultOutputFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedService::getDescription(),
            ]));
        }

        if (Hyde::currentPage() !== null) {
            return static::filterDuplicateMetadata($metadataBag, View::shared('page'));
        }

        return $metadataBag;
    }

    protected static function filterDuplicateMetadata(GlobalMetadataBag $global, HydePage $shared): static
    {
        // Reject any metadata from the global metadata bag that is already present in the page metadata bag.

        $global->links = array_filter($global->links, fn ($link) => !in_array($link, $shared->metadata->links));
        $global->metadata = array_filter($global->metadata, fn ($meta) => !in_array($meta, $shared->metadata->metadata));
        $global->properties = array_filter($global->properties, fn ($property) => !in_array($property, $shared->metadata->properties));
        $global->generics = array_filter($global->generics, fn ($generic) => !in_array($generic, $shared->metadata->generics));

        return $global;
    }
}
