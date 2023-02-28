<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Collection;

/**
 * The PageCollection contains all the instantiated pages.
 *
 * @property array<string, HydePage> $items The pages in the collection.
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
        return $pageClass ? $this->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : $this;
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
        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            $this->discoverPagesFor($pageClass);
        }

        $this->runExtensionCallbacks();

        return $this;
    }

    protected function runExtensionCallbacks(): self
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverPages($this);
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
}
