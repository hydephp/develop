<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;

/**
 * @internal This page is used to render the search index for the documentation.
 */
class DocumentationSearchIndex extends InMemoryPage
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
        parent::__construct('search.json', [
            'navigation' => ['hidden' => true],
        ]);
    }

    public function compile(): string
    {
        return GeneratesDocumentationSearchIndex::generate();
    }

    public function getOutputPath(): string
    {
        return DocumentationPage::outputDirectory().'/search.json';
    }
}
