<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\PublicationService;

use function arsort;

/**
 * Called by the PublicationsExtension::discoverPages method,
 * during the HydePHP autodiscovery boot process.
 *
 * @see \Hyde\Publications\PublicationsExtension::discoverPages()
 * @see \Hyde\Publications\Testing\Feature\GeneratesPublicationTagPagesTest
 */
class GeneratesPublicationTagPages
{
    protected PageCollection $collection;

    public function __construct(PageCollection $collection)
    {
        $this->collection = $collection;
    }

    public function __invoke(): void
    {
        $collection = $this->collection;

        // Retrieve publication types
        $publicationTypes = PublicationService::getPublicationTypes();

        // Initialize arrays to hold tag counts and pages by tag
        $tagCounts = [];
        $pagesByTag = [];

        // Loop through each publication type to retrieve publications and associated tags
        foreach ($publicationTypes as $publicationType) {
            // Retrieve tag fields for the current publication type
            $publicationTagFieldsByName = [];
            foreach ($publicationType->getFields() as $fieldDefinition) {
                if ($fieldDefinition->type->name == 'Tag') {
                    $publicationTagFieldsByName[] = $fieldDefinition->name;
                }
            }

            // Skip the current publication type if no tag fields are found
            if (! $publicationTagFieldsByName) {
                continue;
            }

            // Retrieve publications for the current publication type
            $publications = PublicationService::getPublicationsForType($publicationType);

            // Loop through each publication to retrieve associated tags
            foreach ($publications as $publication) {
                foreach ($publicationTagFieldsByName as $tagFieldName) {
                    $tags = (array) $publication->matter->get($tagFieldName);
                    foreach ($tags as $tag) {
                        // Skip empty tags
                        if (empty($tag)) {
                            continue;
                        }

                        // Increment tag count for the current tag
                        if (! isset($tagCounts[$tag])) {
                            $tagCounts[$tag] = 0;
                        }
                        $tagCounts[$tag]++;

                        // Add the current publication to the list of pages for the current tag
                        if (! isset($pagesByTag[$tag])) {
                            $pagesByTag[$tag] = [];
                        }
                        $pagesByTag[$tag][] = $publication;
                    }
                }
            }
        }

        arsort($tagCounts, SORT_NUMERIC);

        // Build the index tags page
        $indexTagsPage = new InMemoryPage('tags/index', ['tags' => $tagCounts], view: 'hyde-publications::tags_list');
        $pageCollection = $collection;
        $pageCollection->addPage($indexTagsPage);

        // Build individual page lists for each tag
        foreach ($pagesByTag as $tag => $pages) {
            $tagPage = new InMemoryPage(
                "tags/$tag",
                ['tag' => $tag, 'publications' => $pages],
                view: 'hyde-publications::tags_detail'
            );
            $pageCollection->addPage($tagPage);
        }
    }
}
