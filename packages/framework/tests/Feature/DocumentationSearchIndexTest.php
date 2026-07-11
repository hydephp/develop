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
        $this->assertSame('1.x', $page->getDocumentationVersion()->name);
    }

    public function testStaticOutputPathHelper()
    {
        $this->assertSame('docs/search.json', DocumentationSearchIndex::outputPath());
    }

    public function testStaticOutputPathHelperWithVersion()
    {
        config(['docs.versions' => ['1.x']]);

        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::outputPath('1.x'));
        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::outputPath(DocumentationVersions::get('1.x')));
    }

    public function testStaticOutputPathHelperWithCustomOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');
        $this->assertSame('foo/search.json', DocumentationSearchIndex::outputPath());
    }

    public function testStaticOutputPathHelperWithVersionAndCustomOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);
        DocumentationPage::setOutputDirectory('foo');

        $this->assertSame('foo/1.x/search.json', DocumentationSearchIndex::outputPath(DocumentationVersions::get('1.x')));
    }

    public function testStaticOutputPathHelperWithRootOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('');
        $this->assertSame('search.json', DocumentationSearchIndex::outputPath());
    }

    public function testOutputPathForRenderedPageFallsBackToDefaultVersionSearchIndexWhenRenderedDocumentationPageIsUnversioned()
    {
        config(['docs.versions' => ['1.x', '2.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new DocumentationPage('installation'));

        $this->assertSame('docs/2.x/search.json', DocumentationSearchIndex::outputPathForRenderedPage());
    }

    public function testOutputPathForRenderedPageFallsBackToDefaultVersionSearchIndexWhenRenderedPageHasNoDocumentationVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new InMemoryPage('index'));

        $this->assertSame('docs/2.x/search.json', DocumentationSearchIndex::outputPathForRenderedPage());
    }

    public function testOutputPathForRenderedPageFallsBackToUnversionedSearchIndexWhenVersioningIsDisabled()
    {
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new InMemoryPage('index'));

        $this->assertSame('docs/search.json', DocumentationSearchIndex::outputPathForRenderedPage());
    }

    public function testOutputPathForRenderedPageUsesVersionedSearchIndexForVersionedSearchPages()
    {
        config(['docs.versions' => ['1.x']]);
        DocumentationPage::setOutputDirectory('docs');

        Render::setPage(new DocumentationSearchPage(DocumentationVersions::get('1.x')));

        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::outputPathForRenderedPage());
    }
}
