<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Foundation\RouteCollection
 * @see \Hyde\Framework\Testing\Feature\PageCollectionTest
 */
final class PageCollection extends BaseSystemCollection
{
    public function getPage(string $sourcePath): PageContract
    {
        return $this->items[$sourcePath] ?? throw new FileNotFoundException($sourcePath.' in page collection');
    }

    public function getPages(?string $pageClass = null): self
    {
        return ! $pageClass ? $this : $this->filter(function (PageContract $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        });
    }

    protected function runDiscovery(): self
    {
        if (Features::hasBladePages()) {
            $this->discoverPagesFor(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->discoverPagesFor(MarkdownPage::class);
        }

        if (Features::hasBlogPosts()) {
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
     * @param  string<\Hyde\Framework\Contracts\PageContract>  $pageClass
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\PageContract>
     */
    protected function parsePagesFor(string $pageClass): Collection
    {
        $collection = new Collection();

        /** @var PageContract $pageClass */
        foreach ($pageClass::files() as $basename) {
            $collection->push($pageClass::parse($basename));
        }

        return $collection;
    }

    protected function discover(PageContract $page): self
    {
        // Create a new route for the given page, and add it to the index.
        $this->put($page->getSourcePath(), $page);

        return $this;
    }
}
