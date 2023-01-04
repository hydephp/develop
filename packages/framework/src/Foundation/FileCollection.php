<?php

declare(strict_types=1);

namespace Hyde\Foundation;

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
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via one of the facades:
 *
 * @see \Hyde\Foundation\Facades\FileCollection
 * @see \Hyde\Hyde::files()
 *
 * Developer Information:
 *
 * If you are a package developer, and want a custom page class to be discovered,
 * you'll need to add it to this collection sometime before the boot process, before discovery is run.
 * Typically, you would do this in the register method of  a service provider. Hyde will then automatically
 * discover source files for the new page class, and compile them during the build process.
 */
final class FileCollection extends BaseFoundationCollection
{
    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public function getSourceFiles(?string $pageClass = null): self
    {
        return ! $pageClass ? $this->getAllSourceFiles() : $this->getSourceFilesFor($pageClass);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @return \Hyde\Foundation\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public function getSourceFilesFor(string $pageClass): self
    {
        return $this->getAllSourceFiles()->where(fn (SourceFile $file): bool => $file->model === $pageClass);
    }

    /** @return \Hyde\Foundation\FileCollection<\Hyde\Support\Filesystem\SourceFile> */
    public function getAllSourceFiles(): self
    {
        return $this->where(fn (ProjectFile $file): bool => $file instanceof SourceFile);
    }

    /** @return \Hyde\Foundation\FileCollection<\Hyde\Support\Filesystem\MediaFile> */
    public function getMediaFiles(): self
    {
        return $this->where(fn (ProjectFile $file): bool => $file instanceof MediaFile);
    }

    protected function runDiscovery(): self
    {
        if (Features::hasHtmlPages()) {
            $this->discoverFilesFor(HtmlPage::class);
        }

        if (Features::hasBladePages()) {
            $this->discoverFilesFor(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->discoverFilesFor(MarkdownPage::class);
        }

        if (Features::hasMarkdownPosts()) {
            $this->discoverFilesFor(MarkdownPost::class);
        }

        if (Features::hasDocumentationPages()) {
            $this->discoverFilesFor(DocumentationPage::class);
        }

        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            $this->discoverFilesFor($pageClass);
        }

        $this->discoverMediaAssetFiles();

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
