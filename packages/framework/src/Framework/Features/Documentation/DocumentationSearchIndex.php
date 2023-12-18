<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;

use function ltrim;

/**
 * @internal This page is used to render the search index for the documentation.
 */
class DocumentationSearchIndex extends InMemoryPage
{
    /**
     * Create a new DocumentationSearchPage instance.
     */
    public function __construct()
    {
        parent::__construct(static::routeKey(), [
            'navigation' => ['hidden' => true],
        ]);
    }

    public function compile(): string
    {
        return GeneratesDocumentationSearchIndex::generate();
    }

    public function getOutputPath(): string
    {
        return static::routeKey();
    }

    public static function routeKey(): string
    {
        return ltrim(DocumentationPage::outputDirectory().'/search.json', '/');
    }
}
