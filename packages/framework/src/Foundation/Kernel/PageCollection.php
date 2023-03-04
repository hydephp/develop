<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Foundation\Facades\Pages;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Collection;

/**
 * The PageCollection contains all the instantiated pages.
 *
 * @template T of \Hyde\Pages\Concerns\HydePage
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, HydePage> $items The pages in the collection.
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via one of the facades:
 *
 * @see \Hyde\Foundation\Facades\PageCollection
 * @see \Hyde\Hyde::pages()
 */
final class PageCollection extends BaseFoundationCollection
{
    /**
     * This method adds the specified page to the page collection.
     * It can be used by package developers to add a page that will be compiled.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off pages;
     * If you are registering multiple pages, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery.
     *
     * In order for your page to be routable and compilable you must call this method during the boot process,
     * either using a Kernel bootingCallback, or by using a HydeExtension's discovery handler callback.
     */
    public function addPage(HydePage $page): self
    {
        $this->put($page->getSourcePath(), $page);

        return $this;
    }

    protected function runDiscovery(): void
    {
        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            $this->discoverPagesFor($pageClass);
        }
    }

    protected function runExtensionCallbacks(): void
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverPages($this);
        }
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
