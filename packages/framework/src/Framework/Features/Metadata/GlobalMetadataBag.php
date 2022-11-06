<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata;

use Hyde\Facades\Features;
use Hyde\Facades\Meta;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
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
            $metadataBag->add(Meta::link('alternate', Hyde::url(RssFeedGenerator::getFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedGenerator::getDescription(),
            ]));
        }

        if (Hyde::currentPage() !== null) {
            static::filterDuplicateMetadata($metadataBag, View::shared('page'));
        }

        return $metadataBag;
    }

    protected static function filterDuplicateMetadata(GlobalMetadataBag $global, HydePage $page): void
    {
        // Reject any metadata from the global metadata bag that is already present in the page metadata bag.

        foreach (['links', 'metadata', 'properties', 'generics'] as $type) {
            $global->$type = array_filter($global->$type, fn ($meta) => ! in_array($meta->uniqueKey(),
                array_map(fn ($meta) => $meta->uniqueKey(), $page->metadata->$type)
            ));
        }
    }
}
