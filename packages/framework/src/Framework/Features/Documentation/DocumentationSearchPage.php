<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\RouteKey;
use Hyde\Facades\Config;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;

/**
 * @internal This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when the Search feature is enabled.
 * If you want to override this page, you can create a page with the route key "docs/search",
 * then this class will not be applied. For example, `_pages/docs/search.blade.php`.
 *
 * When documentation versioning is enabled, one search page is generated per version,
 * and you can override them with pages matching their route keys, like "docs/1.x/search".
 */
class DocumentationSearchPage extends InMemoryPage
{
    protected readonly ?DocumentationVersion $version;

    /**
     * Create a new DocumentationSearchPage instance.
     */
    public function __construct(?DocumentationVersion $version = null)
    {
        $this->version = $version;

        parent::__construct(static::routeKey($version), [
            'title' => 'Search',
            'navigation' => ['hidden' => true],
            'article' => false,
        ], view: 'hyde::pages.docs.search');
    }

    public static function enabled(?DocumentationVersion $version = null): bool
    {
        return Config::getBool('docs.create_search_page', true) && ! static::anotherSearchPageExists($version);
    }

    /**
     * Get the documentation version this search page belongs to, or null if it does not belong to one.
     */
    public function getDocumentationVersion(): ?DocumentationVersion
    {
        return $this->version;
    }

    public static function routeKey(?DocumentationVersion $version = null): string
    {
        return RouteKey::fromPage(DocumentationPage::class, $version === null ? 'search' : "$version/search")->get();
    }

    protected static function anotherSearchPageExists(?DocumentationVersion $version = null): bool
    {
        // Since routes aren't discovered yet due to this page being added in the core extension,
        // we need to check the page collection directly, instead of the route collection.
        return Hyde::pages()->first(fn (HydePage $file): bool => $file->getRouteKey() === static::routeKey($version)) !== null;
    }
}
