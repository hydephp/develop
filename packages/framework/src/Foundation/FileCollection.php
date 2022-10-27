<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Facades\Features;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\File;

/**
 * @see \Hyde\Framework\Foundation\FileCollection
 */
final class FileCollection extends BaseFoundationCollection
{
    public function getSourceFiles(?string $pageClass = null): self
    {
        return ! $pageClass ? $this->getAllSourceFiles() : $this->getSourceFilesFor($pageClass);
    }

    public function getAllSourceFiles(): self
    {
        return $this->filter(function (File $file) {
            return $file->belongsTo !== null;
        });
    }

    public function getSourceFilesFor(string $pageClass): self
    {
        return $this->filter(function (File $file) use ($pageClass): bool {
            return $file->belongsTo() === $pageClass;
        });
    }

    public function getMediaFiles(): self
    {
        return $this->filter(function (File $file): bool {
            return str_starts_with((string) $file, '_media');
        });
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

        $this->discoverMediaAssetFiles();

        return $this;
    }

    /** @param class-string<HydePage> $pageClass */
    protected function discoverFilesFor(string $pageClass): void
    {
        // Scan the source directory, and directories therein, for files that match the model's file extension.
        foreach (glob($this->kernel->path($pageClass::sourcePath('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $this->put($this->kernel->pathToRelative($filepath), File::make($filepath)->belongsTo($pageClass));
            }
        }
    }

    protected function discoverMediaAssetFiles(): void
    {
        foreach (DiscoveryService::getMediaAssetFiles() as $filepath) {
            $this->put($this->kernel->pathToRelative($filepath), File::make($filepath));
        }
    }
}
