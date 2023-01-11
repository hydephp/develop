<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\PageCollection;
use Hyde\Pages\VirtualPage;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use function range;

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

    /**
     * @deprecated This method will be removed before merging into master.
     *
     * @internal This method will be removed before merging into master.
     */
    protected static function generatePublicationPaginatedListingPagesForType(PublicationType $type,
        PageCollection $instance
    ): void {
        $paginator = $type->getPaginator();

        foreach (range(1, $paginator->totalPages()) as $page) {
            $paginator->setCurrentPage($page);
            $listingPage = new VirtualPage("{$type->getDirectory()}/page-$page", [
                'publicationType' => $type, 'paginatorPage' => $page,
                'title' => $type->name.' - Page '.$page,
            ], view: $type->listTemplate);
            $instance->put($listingPage->getSourcePath(), $listingPage);
        }
    }
}
