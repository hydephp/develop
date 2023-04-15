<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Pages\PublicationPage;

/**
 * Called by the PublicationsExtension::discoverPages method,
 * during the HydePHP autodiscovery boot process.
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
        // Initialize arrays to hold tag counts and pages by tag
        /** @var array<string, array<\Hyde\Publications\Pages\PublicationPage>> $pagesByTag */
        $pagesByTag = [];

        // Set the basename for the tags route (generated pages will be located at /tags/{tag})
        $tagsRouteBasename = 'tags';

        // Loop through each publication to retrieve associated tags
        foreach (PublicationPage::all() as $publication) {
            foreach ($publication->getType()->getFields()->whereStrict('type', PublicationFieldTypes::Tag) as $field) {
                $tags = (array) $publication->matter->get($field->name);
                foreach ($tags as $tag) {
                    if ($tag) {
                        // Add the current publication to the list of pages for the current tag
                        $pagesByTag[$tag][] = $publication;
                    }
                }
            }
        }

        // Build the index tags page
        $this->pageCollection->addPage(new InMemoryPage('tags/index', [
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
