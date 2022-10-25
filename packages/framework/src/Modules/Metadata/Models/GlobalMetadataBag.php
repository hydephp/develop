<?php

namespace Hyde\Framework\Modules\Metadata\Models;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Metadata\MetadataBag;
use Hyde\Framework\Services\RssFeedService;

class GlobalMetadataBag extends MetadataBag
{
    /**
     * @todo #536 Remove duplicate metadata from page;
     */
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

        return $metadataBag;
    }
}
