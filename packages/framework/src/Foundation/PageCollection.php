<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Facades\Features;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Collection;

/**
 * The PageCollection contains all the discovered pages.
 *
 * This class is stored as a singleton in the HydeKernel. You would commonly access it via one of the facades.
 * @example `\Hyde\Hyde::pages()->getPage('_pages/index.md')`
 * @example `Facades\PageCollection::pages()->getPage('_pages/index.md')`
 *
 * @see \Hyde\Foundation\RouteCollection
 * @see \Hyde\Framework\Testing\Feature\PageCollectionTest
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

        return $this;
    }

    protected function discoverPagesFor(string $pageClass): void
    {
        $this->parsePagesFor($pageClass)->each(function ($page) {
            $this->discover($page);
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

    protected function discover(HydePage $page): self
    {
        // Create a new route for the given page, and add it to the index.
        $this->put($page->getSourcePath(), $page);

        return $this;
    }
}
