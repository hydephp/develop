<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RouteKey;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;

/**
 * @internal This page is used to render the search index for the documentation.
 *
 * When documentation versioning is enabled, one search index is generated per version,
 * containing only the pages belonging to that version.
 */
class DocumentationSearchIndex extends InMemoryPage
{
    public static string $outputExtension = '.json';

    protected readonly ?DocumentationVersion $version;

    public function __construct(?DocumentationVersion $version = null)
    {
        $this->version = $version;

        parent::__construct(static::routeKey($version), [
            'navigation' => ['hidden' => true],
        ]);
    }

    public function compile(): string
    {
        return GeneratesDocumentationSearchIndex::handle($this->version);
    }

    /**
     * Get the documentation version this search index belongs to, or null if it does not belong to one.
     */
    public function getDocumentationVersion(): ?DocumentationVersion
    {
        return $this->version;
    }

    /**
     * Get the route key of the search index, which for this page is also its output path.
     */
    public static function routeKey(?DocumentationVersion $version = null): string
    {
        return RouteKey::fromPage(DocumentationPage::class, $version === null ? 'search' : "$version->name/search").'.json';
    }
}
