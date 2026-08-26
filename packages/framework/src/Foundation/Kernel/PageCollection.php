<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\SourceFile;

/**
 * The PageCollection contains all the instantiated pages.
 *
 * @template T of \Hyde\Pages\Concerns\HydePage
 *
 * @extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, HydePage> $items The pages in the collection.
 *
 * @method HydePage|null get(string $key, HydePage $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\PageCollection
 * @see \Hyde\Hyde::pages()
 *
 * Note that a localized site does not duplicate its pages here. A source file is always
 * exactly one page, regardless of how many languages the site is compiled for, as the
 * language variants are created in the route collection, which is the layer that
 * maps out the files that will actually be emitted into the site output.
 *
 * @see \Hyde\Foundation\Kernel\RouteCollection
 */
final class PageCollection extends BaseFoundationCollection
{
    public function addPage(HydePage $page): void
    {
        $this->put($page->getSourcePath(), $page);
    }

    protected function runDiscovery(): void
    {
        $this->kernel->files()->each(function (SourceFile $file): void {
            $this->addPage($this->parsePage($file->pageClass, $file->getPath()));
        });
    }

    protected function runExtensionHandlers(): void
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverPages($this);
        }
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass */
    protected static function parsePage(string $pageClass, string $path): HydePage
    {
        return $pageClass::parse($pageClass::pathToIdentifier($path));
    }

    public function getPage(string $sourcePath): HydePage
    {
        return $this->get($sourcePath) ?? throw new FileNotFoundException($sourcePath);
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass */
    public function getPages(?string $pageClass = null): PageCollection
    {
        return $pageClass ? $this->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : $this;
    }
}
