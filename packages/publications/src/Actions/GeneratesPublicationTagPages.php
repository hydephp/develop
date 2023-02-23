<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Foundation\Kernel\PageCollection;

/**
 * Called by the PublicationsExtension::discoverPages method,
 * during the HydePHP autodiscovery boot process.
 *
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

        // Retrieve publication types and publication tags
        $publicationTypes = \Hyde\Publications\PublicationService::getPublicationTypes();
        $tagGroups = new \Hyde\Publications\Models\PublicationTags();

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
            $publications = \Hyde\Publications\PublicationService::getPublicationsForPubType($publicationType);

            // Loop through each publication to retrieve associated tags
            foreach ($publications as $publication) {
                foreach ($publicationTagFieldsByName as $tagFieldName) {
                    $tags = (array) $publication->matter->get($tagFieldName);
                    foreach ($tags as $tag) {
                        // Increment tag count for the current tag
                        if (! isset($tagCounts[$tag])) {
                            $tagCounts[$tag] = 0;
                        }
                        $tagCounts[$tag]++;

                        // Add the current publication to the list of pages for the current tag
                        if (! isset($pagesByTag[$tag])) {
                            $pagesByTag[$tag] = [];
                        }
                        $pagesByTag[$tag][] = $publication->getIdentifier();
                    }
                }
            }
        }

        // Build the index tags page
        $indexTagsPage = new \Hyde\Pages\InMemoryPage('tags/index', ['tagCounts' => $tagCounts], 'blah', 'pages/tags.blade.php');
        $pageCollection = $collection;
        $pageCollection->addPage($indexTagsPage);

        // Build individual page lists for each tag
        foreach ($pagesByTag as $tag => $pages) {
            $tagPage = new \Hyde\Pages\InMemoryPage(
                "tags/$tag",
                ['tag' => $tag, 'pages' => $pages],
                'blah',
                'pages/tagPageList.blade.php'
            );
            $pageCollection->addPage($tagPage);
        }
    }
}
