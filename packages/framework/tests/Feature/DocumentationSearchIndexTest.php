<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSearchCommandTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Documentation\DocumentationSearchIndex::class)]
class DocumentationSearchIndexTest extends TestCase
{
    public function testCanCreateDocumentationSearchIndexInstance()
    {
        $this->assertInstanceOf(DocumentationSearchIndex::class, new DocumentationSearchIndex());
    }

    public function testRouteKeyIsSetToDocumentationOutputDirectory()
    {
        $page = new DocumentationSearchIndex();
        $this->assertSame('docs/search.json', $page->routeKey);
    }

    public function testRouteKeyIsSetToConfiguredDocumentationOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');

        $page = new DocumentationSearchIndex();
        $this->assertSame('foo/search.json', $page->routeKey);
    }

    public function testRouteKeyIsSetToVersionedDocumentationOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);

        $page = new DocumentationSearchIndex(DocumentationVersions::get('1.x'));

        $this->assertSame('docs/1.x/search.json', $page->routeKey);
        $this->assertSame('docs/1.x/search.json', $page->getOutputPath());
        $this->assertSame($page::outputPath($page->getIdentifier()), $page->getOutputPath());
        $this->assertSame('1.x', $page->getDocumentationVersion()->name);
    }

    public function testStaticRouteKeyHelper()
    {
        $this->assertSame('docs/search.json', DocumentationSearchIndex::routeKey());
    }

    public function testStaticRouteKeyHelperWithVersion()
    {
        config(['docs.versions' => ['1.x']]);

        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::get('1.x')));
    }

    public function testStaticRouteKeyHelperWithCustomOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');
        $this->assertSame('foo/search.json', DocumentationSearchIndex::routeKey());
    }

    public function testStaticRouteKeyHelperWithVersionAndCustomOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);
        DocumentationPage::setOutputDirectory('foo');

        $this->assertSame('foo/1.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::get('1.x')));
    }

    public function testStaticRouteKeyHelperWithRootOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('');
        $this->assertSame('search.json', DocumentationSearchIndex::routeKey());
    }

    public function testCurrentVersionSearchIndexPathFallsBackToDefaultVersionSearchIndexWhenRenderedDocumentationPageIsUnversioned()
    {
        config(['docs.versions' => ['1.x', '2.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new DocumentationPage('installation'));

        $this->assertSame('docs/2.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }

    public function testCurrentVersionSearchIndexPathFallsBackToDefaultVersionSearchIndexWhenRenderedPageHasNoDocumentationVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new InMemoryPage('index'));

        $this->assertSame('docs/2.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }

    public function testCurrentVersionSearchIndexPathFallsBackToUnversionedSearchIndexWhenVersioningIsDisabled()
    {
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new InMemoryPage('index'));

        $this->assertSame('docs/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }

    public function testCurrentVersionSearchIndexPathUsesVersionedSearchIndexForVersionedSearchPages()
    {
        config(['docs.versions' => ['1.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new DocumentationSearchPage(DocumentationVersions::get('1.x')));

        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }
}
