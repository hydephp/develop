<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Hyde;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\BuildWarnings;
use Hyde\Support\Models\Redirect;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Facades\Features;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

use function Hyde\unslash;
use function array_filter;
use function array_keys;
use function sprintf;

class HydeCoreExtension extends HydeExtension
{
    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return array_keys(array_filter([
            HtmlPage::class => Features::hasHtmlPages(),
            BladePage::class => Features::hasBladePages(),
            MarkdownPage::class => Features::hasMarkdownPages(),
            MarkdownPost::class => Features::hasMarkdownPosts(),
            DocumentationPage::class => Features::hasDocumentationPages(),
        ], fn (bool $value): bool => $value));
    }

    public function discoverFiles(FileCollection $collection): void
    {
        if (DocumentationVersions::enabled()) {
            $this->discardUnversionedDocumentationFiles($collection);
        }
    }

    public function discoverPages(PageCollection $collection): void
    {
        $default = DocumentationVersions::default();

        if ($default !== null && Features::hasDocumentationPages()) {
            $this->discoverDocumentationRootRedirect($collection, $default);
        }

        if (Features::hasDocumentationSearch()) {
            if (DocumentationVersions::enabled()) {
                // When documentation versioning is enabled, each version gets its own search index and search page.
                DocumentationVersions::all()->each(function (DocumentationVersion $version) use ($collection): void {
                    $collection->addPage(new DocumentationSearchIndex($version));

                    if (DocumentationSearchPage::enabled($version)) {
                        $collection->addPage(new DocumentationSearchPage($version));
                    }
                });
            } else {
                $collection->addPage(new DocumentationSearchIndex());

                if (DocumentationSearchPage::enabled()) {
                    $collection->addPage(new DocumentationSearchPage());
                }
            }
        }
    }

    /** Discard documentation source files stored outside the version directories. */
    protected function discardUnversionedDocumentationFiles(FileCollection $collection): void
    {
        $collection->getFiles(DocumentationPage::class)->each(function (SourceFile $file) use ($collection): void {
            if (DocumentationVersions::fromIdentifier(DocumentationPage::pathToIdentifier($file->getPath())) === null) {
                $collection->forget($file->getPath());

                // Warn because enabling versioning would otherwise silently drop existing documentation files.
                BuildWarnings::report(sprintf('Ignoring unversioned documentation file "%s" as documentation versioning is enabled. Move it into a registered version directory to include it in the site.', $file->getPath()));
            }
        });
    }

    /** Add the documentation root redirect unless the route is user-defined. */
    protected function discoverDocumentationRootRedirect(PageCollection $collection, DocumentationVersion $default): void
    {
        $routeKey = unslash(DocumentationPage::outputDirectory().'/index');

        $rootRouteExists = $this->hasPageWithRouteKey($collection, $routeKey);
        $defaultHomeExists = $this->hasPageWithRouteKey($collection, $default->homeRouteName());

        if ($defaultHomeExists && ! $rootRouteExists) {
            $collection->addPage(new Redirect($routeKey, Hyde::formatLink("$default->name/index.html"), matter: [
                'navigation' => ['hidden' => true],
            ]));
        }
    }

    protected function hasPageWithRouteKey(PageCollection $collection, string $routeKey): bool
    {
        return $collection->first(fn (HydePage $page): bool => $page->getRouteKey() === $routeKey) !== null;
    }
}
