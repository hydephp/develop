<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Support\Filesystem\ProjectFile;
use Hyde\Support\Filesystem\SourceFile;

/**
 * The FileCollection contains all the discovered source and media files,
 * and thus has an integral role in the Hyde Auto Discovery process.
 *
 * @property array<string, ProjectFile> $items The files in the collection.
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
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public function getSourceFiles(?string $pageClass = null): self
    {
        return ! $pageClass ? $this->getAllSourceFiles() : $this->getSourceFilesFor($pageClass);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public function getSourceFilesFor(string $pageClass): self
    {
        return $this->getAllSourceFiles()->where(fn (SourceFile $file): bool => $file->model === $pageClass);
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile> */
    public function getAllSourceFiles(): self
    {
        return $this->where(fn (ProjectFile $file): bool => $file instanceof SourceFile);
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\MediaFile> */
    public function getMediaFiles(): self
    {
        return $this->where(fn (ProjectFile $file): bool => $file instanceof MediaFile);
    }

    protected function runDiscovery(): self
    {
        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            $this->discoverFilesFor($pageClass);
        }

        $this->runExtensionCallbacks();

        $this->discoverMediaAssetFiles();

        return $this;
    }

    protected function runExtensionCallbacks(): self
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getRegisteredExtensions() as $extension) {
            $extension::discoverFiles($this);
        }

        return $this;
    }

    /** @param class-string<HydePage> $pageClass */
    protected function discoverFilesFor(string $pageClass): void
    {
        // Scan the source directory, and directories therein, for files that match the model's file extension.
        foreach (glob($this->kernel->path($pageClass::sourcePath('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename((string) $filepath), '_')) {
                $this->put($this->kernel->pathToRelative($filepath), SourceFile::make($filepath, $pageClass));
            }
        }
    }

    protected function discoverMediaAssetFiles(): void
    {
        foreach (DiscoveryService::getMediaAssetFiles() as $filepath) {
            $this->put($this->kernel->pathToRelative($filepath), MediaFile::make($filepath));
        }
    }
}
