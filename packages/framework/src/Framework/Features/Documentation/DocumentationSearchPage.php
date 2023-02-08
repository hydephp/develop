<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;

/**
 * @internal This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when the Search feature is enabled.
 * If you want to override this page, you can create a page with the route key "docs/search",
 * then this class will not be applied. For example, `_pages/docs/search.blade.php`.
 *
 * @see \Hyde\Framework\Testing\Feature\DocumentationSearchPageTest
 */
class DocumentationSearchPage extends DocumentationPage
{
    public function __construct()
    {
        parent::__construct(DocumentationPage::outputDirectory().'/search', [
            'title' => 'Search',
        ]);
    }

    public static function enabled(): bool
    {
        return config('docs.create_search_page', true) && ! Hyde::routes()->has('docs/search');
    }

    public static function generate(): string
    {
        return (new StaticPageBuilder(new static()))->__invoke();
    }

    public function compile(): string
    {
        return view('hyde::pages.documentation-search')->render();
    }
}
