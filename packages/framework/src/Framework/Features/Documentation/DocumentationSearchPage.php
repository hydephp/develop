<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\VirtualPage;

/**
 * @see \Hyde\Framework\Testing\Feature\DocumentationSearchPageTest
 */
class DocumentationSearchPage extends VirtualPage
{
    public function __construct()
    {
        parent::__construct(DocumentationPage::outputDirectory().'/search');
    }
}
