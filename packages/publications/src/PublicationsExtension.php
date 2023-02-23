<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use function range;
use function str_ends_with;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationsExtensionTest
 */
class PublicationsExtension extends HydeExtension
{
    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return [
            PublicationPage::class,
            PublicationListPage::class,
        ];
    }

    public static function discoverPages(PageCollection $collection): void
    {
        static::discoverPublicationPages($collection);

        if (Filesystem::exists('tags.yml')) {
            static::generatePublicationTagPages($collection);
        }
    }

    protected static function discoverPublicationPages(PageCollection $instance): void
    {
        PublicationService::getPublicationTypes()->each(function (PublicationType $type) use ($instance): void {
            static::discoverPublicationPagesForType($type, $instance);
            static::generatePublicationListingPageForType($type, $instance);
        });
    }

    protected static function discoverPublicationPagesForType(PublicationType $type, PageCollection $instance): void
    {
        PublicationService::getPublicationsForPubType($type)->each(function (PublicationPage $publication) use (
            $instance
        ): void {
            $instance->addPage($publication);
        });
    }

    protected static function generatePublicationListingPageForType(PublicationType $type, PageCollection $instance): void
    {
        $page = new PublicationListPage($type);
        $instance->put($page->getSourcePath(), $page);

        if ($type->usesPagination()) {
            static::generatePublicationPaginatedListingPagesForType($type, $instance);
        }
    }

    protected static function generatePublicationPaginatedListingPagesForType(PublicationType $type,
        PageCollection $instance
    ): void {
        $paginator = $type->getPaginator();

        foreach (range(1, $paginator->totalPages()) as $page) {
            $paginator->setCurrentPage($page);
            $listTemplate = $type->listTemplate;
            if (str_ends_with($listTemplate, '.blade.php')) {
                $listTemplate = "{$type->getDirectory()}/$listTemplate";
            }
            $listingPage = new InMemoryPage("{$type->getDirectory()}/page-$page", [
                'publicationType' => $type, 'paginatorPage' => $page,
                'title' => $type->name.' - Page '.$page,
            ], view: $listTemplate);
            $instance->put($listingPage->getSourcePath(), $listingPage);
        }
    }

    protected static function generatePublicationTagPages(PageCollection $collection): void
    {
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
