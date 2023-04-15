<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Publications;

use function array_map;

/**
 * Called by the PublicationsExtension::discoverPages method,
 * during the HydePHP autodiscovery boot process.
 *
 * @todo: Refactor to use page models, or at the very least, view components
 *
 * @see \Hyde\Publications\PublicationsExtension::discoverPages()
 * @see \Hyde\Publications\Testing\Feature\GeneratesPublicationTagPagesTest
 */
class GeneratesPublicationTagPages
{
    protected PageCollection $pageCollection;

    public function __construct(PageCollection $collection)
    {
        $this->pageCollection = $collection;
    }

    public function __invoke(): void
    {
        // Set the basename for the tags route (generated pages will be located at /tags/{tag})
        $tagsRouteBasename = 'tags';

        $pagesByTag = Publications::getPublicationsGroupedByTags();

        // Build the index tags page
        $this->pageCollection->addPage(new InMemoryPage("$tagsRouteBasename/index", [
            /** @var array<string, int> $tags */
            'tags' => array_map('count', $pagesByTag),
        ], view: 'hyde-publications::tags_list'));

        // Build individual page lists for each tag
        foreach ($pagesByTag as $tag => $pages) {
            $this->pageCollection->addPage(new InMemoryPage(
                "$tagsRouteBasename/$tag",
                ['tag' => $tag, 'publications' => $pages],
                view: 'hyde-publications::tags_detail'
            ));
        }
    }
}
