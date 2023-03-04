<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\SourceFile;

/**
 * The FileCollection contains all the discovered source and media files,
 * and thus has an integral role in the Hyde Auto Discovery process.
 *
 * @template T of \Hyde\Support\Filesystem\SourceFile
 * @template-extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, SourceFile> $items The files in the collection.
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via one of the facades:
 *
 * @see \Hyde\Foundation\Facades\Files
 * @see \Hyde\Hyde::files()
 */
final class FileCollection extends BaseFoundationCollection
{
    /**
     * This method adds the specified file to the file collection.
     * It can be used by package developers to add a file that can be discovered.
     *
     * In order for your file to be further processed you must call this method during the boot process,
     * either using a Kernel bootingCallback, or by using a HydeExtension's discovery handler callback.
     */
    public function addFile(SourceFile $file): void
    {
        $this->put($file->getPath(), $file);
    }

    protected function runDiscovery(): void
    {
        /** @var class-string<\Hyde\Pages\Concerns\HydePage> $pageClass */
        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            if ($pageClass::isDiscoverable()) {
                $this->discoverFilesFor($pageClass);
            }
        }
    }

    protected function runExtensionCallbacks(): void
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverFiles($this);
        }
    }

    /** @param class-string<HydePage> $pageClass */
    protected function discoverFilesFor(string $pageClass): void
    {
        // Scan the source directory, and directories therein, for files that match the model's file extension.
        foreach (glob($this->kernel->path($pageClass::sourcePath('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename((string) $filepath), '_')) {
                $this->addFile(SourceFile::make($filepath, $pageClass));
            }
        }
    }
}
