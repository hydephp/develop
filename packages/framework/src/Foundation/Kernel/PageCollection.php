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
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, HydePage> $items The pages in the collection.
 *
 * @method HydePage get(string $key, HydePage $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\PageCollection
 * @see \Hyde\Hyde::pages()
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
    protected static function parsePage(string $pageClass, string $path)
    {
        return $pageClass::parse($pageClass::pathToIdentifier($path));
    }

    public function getPage(string $sourcePath): HydePage
    {
        return $this->get($sourcePath) ?? throw new FileNotFoundException($sourcePath);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\Kernel\PageCollection<string, \Hyde\Pages\Concerns\HydePage>
     */
    public function getPages(?string $pageClass = null): PageCollection
    {
        return $pageClass ? $this->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : $this;
    }
}
