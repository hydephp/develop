<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Illuminate\Support\Collection;

class PageCollection extends Collection
{
    protected HydeKernelContract $kernel;

    public function __construct(HydeKernelContract $kernel)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->discoverPages();
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

    protected function discoverPages(): static
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

    protected function discover(PageContract $page): static
    {
        // Create a new route for the given page, and add it to the index.
        $this->put($page->getSourcePath(), $page);

        return $this;
    }
}
