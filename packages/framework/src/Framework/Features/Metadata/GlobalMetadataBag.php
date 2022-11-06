<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata;

use Hyde\Facades\Features;
use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\Elements\MetadataElement;
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
        $metadata = new self();

        foreach (config('hyde.meta', []) as $item) {
            $metadata->add($item);
        }

        if (Features::sitemap()) {
            $metadata->add(Meta::link('sitemap', Hyde::url('sitemap.xml'), [
                'type' => 'application/xml', 'title' => 'Sitemap',
            ]));
        }

        if (Features::rss()) {
            $metadata->add(Meta::link('alternate', Hyde::url(RssFeedGenerator::getFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedGenerator::getDescription(),
            ]));
        }

        if (Hyde::currentPage() !== null) {
            static::filterDuplicateMetadata($metadata, View::shared('page'));
        }

        return $metadata;
    }

    protected static function filterDuplicateMetadata(GlobalMetadataBag $global, HydePage $page): void
    {
        // Reject any metadata from the global metadata bag that is already present in the page metadata bag.

        foreach (['links', 'metadata', 'properties', 'generics'] as $type) {
            $global->$type = array_filter($global->$type, fn (MetadataElement $element) => ! in_array($element->uniqueKey(),
                array_map(fn (MetadataElement $element) => $element->uniqueKey(), $page->metadata->$type)
            ));
        }
    }
}
