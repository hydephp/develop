<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\VirtualPage;

/**
 * This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when Search is enabled.
 * If you want to override the search page, you can create a file at: `_pages/docs/search.blade.php`,
 * then this class will not be applied.
 *
 * @see \Hyde\Framework\Testing\Feature\DocumentationSearchPageTest
 */
class DocumentationSearchPage extends VirtualPage
{
    public function __construct()
    {
        parent::__construct(DocumentationPage::outputDirectory().'/search', ['title' => 'Search'], view: 'hyde::pages.documentation-search');
    }
}
