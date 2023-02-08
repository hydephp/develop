<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;

/**
 * This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when Search is enabled.
 * If you want to override the search page, you can create a file at: `_pages/docs/search.blade.php`,
 * then this class will not be applied.
 *
 * @see \Hyde\Framework\Testing\Feature\DocumentationSearchPageTest
 *
 * @internal
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
        return config('docs.create_search_page', true);
    }

    public static function generate(): string
    {
        $page = new static();
        $path = Hyde::sitePath($page->getOutputPath());
        Filesystem::ensureDirectoryExists(dirname($path));
        Hyde::shareViewData($page);
        Filesystem::putContents($path, $page->compile());

        return $path;
    }

    public function compile(): string
    {
        return view('hyde::pages.documentation-search')->render();
    }
}
