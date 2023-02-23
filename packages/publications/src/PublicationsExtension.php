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
        $publicationTypes = \Hyde\Publications\PublicationService::getPublicationTypes();
        //dump("PUBLICATION TYPES", $publicationTypes);
        $tagGroups = new \Hyde\Publications\Models\PublicationTags();
        foreach ($tagGroups->getTags() as $tagGroup) {
            //dump("TAGS", $tagGroup);
        }
        foreach ($publicationTypes as $publicationType) {
            $publications = \Hyde\Publications\PublicationService::getPublicationsForPubType($publicationType);
            //dump("PUBLICATIONS FOR $publicationType->name", $publications);
            //dump($publications);
        }

        $tagCounts = [];
        $pagesByTag = [];

        foreach ($publicationTypes as $publicationType) {
            $pubTagFieldsByName = [];
            foreach ($publicationType->getFields() as $fieldDefinition) {
                if ($fieldDefinition->type->name == 'Tag') {
                    $pubTagFieldsByName[] = $fieldDefinition->name;
                }
            }

            if (! $pubTagFieldsByName) {
                continue;
            }

            $publications = \Hyde\Publications\PublicationService::getPublicationsForPubType($publicationType);
            foreach ($publications as $publication) {
                foreach ($pubTagFieldsByName as $tagFieldName) {
                    $tags = (array) $publication->matter->data[$tagFieldName];
                    foreach ($tags as $tag) {
                        if (! isset($tagCounts[$tag])) {
                            $tagCounts[$tag] = 0;
                        }
                        $tagCounts[$tag]++;

                        if (! isset($pagesByTag[$tag])) {
                            $pagesByTag[$tag] = [];
                        }
                        $pagesByTag[$tag][] = $publication->getIdentifier();
                    }
                }
            }
        }

        dump('TAG COUNTS', $tagCounts);
        echo "\n\n\n";
        dump('PAGES BY TAG', $pagesByTag);

        // Build main/single tags page
        $page = new \Hyde\Pages\InMemoryPage('tags/index.html', ['tagCounts' => $tagCounts], 'blah', 'pages/tags.blade.php');
        $pageCollection = $collection;
        $pageCollection->addPage($page);

        // Now build the individual page lists for each tag
        foreach ($pagesByTag as $tag => $pages) {
            $page = new \Hyde\Pages\InMemoryPage(
                "tags/$tag.html",
                ['tag' => $tag, 'pages' => $pages],
                'blah',
                'pages/tagPageList.blade.php'
            );
            $pageCollection = \Hyde\Hyde::pages();
            $pageCollection->addPage($page);
        }
    }
}
