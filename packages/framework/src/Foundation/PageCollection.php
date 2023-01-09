<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use function glob;
use Hyde\Facades\Features;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\PublicationPage;
use Hyde\Pages\VirtualPage;
use Illuminate\Support\Collection;

/**
 * The PageCollection contains all the instantiated pages.
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via one of the facades:
 *
 * @todo We could improve this by catching exceptions and rethrowing them using a
 *       DiscoveryException to make it clear that the problem is with the discovery process.
 *
 * @see \Hyde\Foundation\Facades\PageCollection
 * @see \Hyde\Hyde::pages()
 */
final class PageCollection extends BaseFoundationCollection
{
    public function getPage(string $sourcePath): HydePage
    {
        return $this->items[$sourcePath] ?? throw new FileNotFoundException($sourcePath.' in page collection');
    }

    public function getPages(?string $pageClass = null): self
    {
        return ! $pageClass ? $this : $this->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        });
    }

    /**
     * This method adds the specified page to the page collection.
     * It can be used by package developers to add a page that will be compiled.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off pages;
     * If you are registering multiple pages, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery.
     *
     * When using this method, take notice of the following things:
     * 1. Be sure to register the page before the HydeKernel boots,
     *    otherwise it might not be fully processed by Hyde.
     * 2. Note that all pages will have their routes added to the route index,
     *    and subsequently be compiled during the build process.
     */
    public function addPage(HydePage $page): self
    {
        $this->put($page->getSourcePath(), $page);

        return $this;
    }

    protected function runDiscovery(): self
    {
        if (Features::hasHtmlPages()) {
            $this->discoverPagesFor(HtmlPage::class);
        }

        if (Features::hasBladePages()) {
            $this->discoverPagesFor(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->discoverPagesFor(MarkdownPage::class);
        }

        if (Features::hasMarkdownPosts()) {
            $this->discoverPagesFor(MarkdownPost::class);
        }

        if (Features::hasDocumentationPages()) {
            $this->discoverPagesFor(DocumentationPage::class);
        }

        if (Features::hasPublicationPages()) {
            $this->discoverPublicationPages();
        }

        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            $this->discoverPagesFor($pageClass);
        }

        return $this;
    }

    protected function discoverPagesFor(string $pageClass): void
    {
        $this->parsePagesFor($pageClass)->each(function (HydePage $page): void {
            $this->addPage($page);
        });
    }

    /**
     * @param  string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @return \Illuminate\Support\Collection<\Hyde\Pages\Concerns\HydePage>
     */
    protected function parsePagesFor(string $pageClass): Collection
    {
        $collection = new Collection();

        /** @var HydePage $pageClass */
        foreach ($pageClass::files() as $basename) {
            $collection->push($pageClass::parse($basename));
        }

        return $collection;
    }

    protected function discoverPublicationPages(): void
    {
        PublicationService::getPublicationTypes()->each(function (PublicationType $type): void {
            $this->discoverPublicationPagesForType($type);
            $this->generatePublicationListingPageForType($type);
        });
    }

    protected function discoverPublicationPagesForType(PublicationType $type): void
    {
        $collection = Collection::make(glob(Hyde::path($type->getDirectory().'/*.md')))->map(function (string $file): PublicationPage {
            return PublicationService::parsePublicationFile(Hyde::pathToRelative($file));
        });

        $collection->each(function (PublicationPage $publication): void {
            $this->addPage($publication);
        });
    }

    protected function generatePublicationListingPageForType(PublicationType $type): void
    {
        $page = new PublicationListPage($type);
        $this->put($page->getSourcePath(), $page);

        return; // return early as this is broken as this requires access to the pages which is currently being constructed. We prob want this in a booted callback

        if ($type->usesPagination()) {
            $this->generatePublicationPaginatedListingPagesForType($type);
        }
    }

    /**
     * @deprecated This method will be removed before merging into master.
     *
     * @internal This method will be removed before merging into master.
     */
    protected function generatePublicationPaginatedListingPagesForType(PublicationType $type): void
    {
        $paginator = $type->getPaginator();

        foreach (range(1, $paginator->totalPages()) as $page) {
            $paginator->setCurrentPage($page);
            $listingPage = new VirtualPage("{$type->getDirectory()}/page-$page", [
                'publicationType' => $type, 'paginatorPage' => $page,
                'title' => $type->name.' - Page '.$page,
            ], view: $type->listTemplate);
            $this->put($listingPage->getSourcePath(), $listingPage);
        }
    }
}
