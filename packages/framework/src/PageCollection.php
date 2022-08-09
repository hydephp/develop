<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\PageCollectionTest
 */
final class PageCollection extends Collection
{
    public static function boot(): self
    {
        return (new self())->discoverPages();
    }

    protected function __construct($items = [])
    {
        parent::__construct($items);
    }

    public function getPage(string $sourcePath): PageContract
    {
        return $this->firstWhere('sourcePath', $sourcePath);
    }

    public function getPages(string $pageClass): Collection
    {
        return $this->filter(function (PageContract $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        });
    }

    protected function discoverPages(): self
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
        // @todo Parse the pages here

        /** @var PageContract $pageClass */
        $pageClass::all()->each(function ($page) {
            $this->discover($page);
        });
    }

    protected function discover(PageContract $page): self
    {
        // Create a new route for the given page, and add it to the index.
        $this->put($page->getSourcePath(), $page);

        return $this;
    }
}
