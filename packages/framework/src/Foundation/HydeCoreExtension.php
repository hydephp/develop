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

    /**
     * When documentation versioning is enabled, all documentation pages belong to a version, so any
     * source files stored outside the version directories are not part of the site, and are ignored.
     *
     * If you want a page at the documentation root, you can create one in the normal page source
     * directory instead, for example `_pages/docs/index.md`, which overrides the root redirect.
     */
    protected function discardUnversionedDocumentationFiles(FileCollection $collection): void
    {
        $collection->getFiles(DocumentationPage::class)->each(function (SourceFile $file) use ($collection): void {
            $identifier = DocumentationPage::pathToIdentifier($file->getPath());

            if (DocumentationVersions::fromIdentifier($identifier) === null) {
                $collection->forget($file->getPath());
            }
        });
    }

    /**
     * When documentation versioning is enabled, the documentation root redirects to the default
     * version's index page, so that inbound links to the docs root always have a destination.
     * Creating your own page with the `docs/index` route key overrides the generated redirect,
     * and the redirect is of course only added when the default version has an index page.
     */
    protected function discoverDocumentationRootRedirect(PageCollection $collection, DocumentationVersion $default): void
    {
        $routeKey = unslash(DocumentationPage::outputDirectory().'/index');

        $taken = $this->hasPageWithRouteKey($collection, $routeKey);

        // There's nothing to redirect to if the default version has no index page.
        $exists = $this->hasPageWithRouteKey($collection, $default->homeRouteName());

        if ($exists && ! $taken) {
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
