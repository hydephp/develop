<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Publications;

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
        // Retrieve publication types
        $publicationTypes = Publications::getPublicationTypes();

        // Initialize arrays to hold tag counts and pages by tag
        /** @var array<string, array<\Hyde\Publications\Pages\PublicationPage>> $pagesByTag */
        $pagesByTag = [];

        // Set the basename for the tags route (generated pages will be located at /tags/{tag})
        $tagsRouteBasename = 'tags';

        // Loop through each publication type to retrieve publications and associated tags
        foreach ($publicationTypes as $publicationType) {
            // Retrieve tag fields for the current publication type
            $publicationTagFieldsByName = [];
            foreach ($publicationType->getFields() as $fieldDefinition) {
                if ($fieldDefinition->type === PublicationFieldTypes::Tag) {
                    $publicationTagFieldsByName[] = $fieldDefinition->name;
                }
            }

            // Only continue with current publication type if tag fields are found
            if ($publicationTagFieldsByName) {
                // Retrieve publications for the current publication type
                $publications = Publications::getPublicationsForType($publicationType);

                // Loop through each publication to retrieve associated tags
                foreach ($publications as $publication) {
                    foreach ($publicationTagFieldsByName as $tagFieldName) {
                        $tags = (array) $publication->matter->get($tagFieldName);
                        foreach ($tags as $tag) {
                            if ($tag) {
                                // Add the current publication to the list of pages for the current tag
                                if (! isset($pagesByTag[$tag])) {
                                    $pagesByTag[$tag] = [];
                                }
                                $pagesByTag[$tag][] = $publication;
                            }
                        }
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
