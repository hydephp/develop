<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\File;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;

final class FileCollection extends BaseSystemCollection
{
    protected function runDiscovery(): self
    {
        if (Features::hasBladePages()) {
            $this->discoverFilesFor(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->discoverFilesFor(MarkdownPage::class);
        }

        if (Features::hasBlogPosts()) {
            $this->discoverFilesFor(MarkdownPost::class);
        }

        if (Features::hasDocumentationPages()) {
            $this->discoverFilesFor(DocumentationPage::class);
        }

        return $this;
    }

    /** @param string<AbstractPage> $pageClass */
    protected function discoverFilesFor(string $pageClass): void
    {
        foreach (glob($this->kernel->path($pageClass::qualifyBasename('{*,**/*}')), GLOB_BRACE) as $filepath) {
            if (! str_starts_with(basename($filepath), '_')) {
                $this->put($this->kernel->pathToRelative($filepath), File::make($filepath));
            }
        }
    }
}
