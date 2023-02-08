<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\VirtualPage;

/**
 * This page is used to render the search page for the documentation.
 *
 * It is not based on a source file, but is dynamically generated when Search is enabled.
 * @see \Hyde\Framework\Testing\Feature\DocumentationSearchPageTest
 */
class DocumentationSearchPage extends VirtualPage
{
    public function __construct()
    {
        parent::__construct(DocumentationPage::outputDirectory().'/search');
    }
}
