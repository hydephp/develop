<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Facades\Config;

use function ltrim;

/**
 * @internal This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when the Search feature is enabled.
 * If you want to override this page, you can create a page with the route key "docs/search",
 * then this class will not be applied. For example, `_pages/docs/search.blade.php`.
 */
class DocumentationSearchPage extends InMemoryPage
{
    /**
     * Generate the search page and save it to disk.
     *
     * @return string The path to the generated file.
     */
    public static function generate(): string
    {
        return StaticPageBuilder::handle(new static());
    }

    /**
     * Create a new DocumentationSearchPage instance.
     */
    public function __construct()
    {
        parent::__construct(static::routeKey(), [
            'title' => 'Search',
            'navigation' => ['hidden' => true],
            'document' => $this->makeDocument(),
        ], view: 'hyde::pages.documentation-search');
    }

    public static function enabled(): bool
    {
        return Config::getBool('docs.create_search_page', true) && ! static::anotherSearchPageExists();
    }

    public static function routeKey(): string
    {
        return ltrim(DocumentationPage::outputDirectory().'/search');
    }

    protected static function anotherSearchPageExists(): bool
    {
        // Since routes aren't discovered yet due to this page being added in the core extension,
        // we need to check the page collection directly, instead of the route collection.
        return Hyde::pages()->first(fn (HydePage $file): bool => $file->getRouteKey() === static::routeKey()) !== null;
    }

    /** @experimental Fixes type issue {@see https://github.com/hydephp/develop/commit/37f7046251b8c0514b8d8ef821de4ef3d35bbac8#commitcomment-135026537} */
    protected function makeDocument(): SemanticDocumentationArticle
    {
        return SemanticDocumentationArticle::make(new DocumentationPage());
    }
}
