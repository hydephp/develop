<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RouteKey;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;

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

    public static function outputPath(string $identifier): string
    {
        return static::routeKey();
    }

    public static function routeKey(): string
    {
        return RouteKey::fromPage(DocumentationPage::class, 'search').'.json';
    }
}
