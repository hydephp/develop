<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RouteKey;
use Hyde\Support\Facades\Render;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\HasDocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

/**
 * @internal This page is used to render the search index for the documentation.
 *
 * When documentation versioning is enabled, one search index is generated per version,
 * containing only the pages belonging to that version.
 */
class DocumentationSearchIndex extends InMemoryPage implements HasDocumentationVersion
{
    protected readonly ?DocumentationVersion $version;

    /**
     * Create a new DocumentationSearchIndex instance.
     */
    public function __construct(?DocumentationVersion $version = null)
    {
        $this->version = $version;

        parent::__construct(static::outputPath($version), [
            'navigation' => ['hidden' => true],
        ]);
    }

    public function compile(): string
    {
        return GeneratesDocumentationSearchIndex::handle($this->version);
    }

    public function getDocumentationVersion(): ?DocumentationVersion
    {
        return $this->version;
    }

    public static function outputPath(DocumentationVersion|string|null $version = null): string
    {
        return RouteKey::fromPage(DocumentationPage::class, $version === null ? 'search' : "$version/search").'.json';
    }

    public function getOutputPath(): string
    {
        return static::outputPath($this->version);
    }

    /**
     * Get the search index path for the page currently being rendered, so that documentation
     * pages in a version use the search index belonging to that version.
     */
    public static function outputPathForRenderedPage(): string
    {
        $page = Render::getPage();

        $version = $page instanceof HasDocumentationVersion ? $page->getDocumentationVersion() : null;

        return static::outputPath($version ?? DocumentationVersions::default());
    }
}
