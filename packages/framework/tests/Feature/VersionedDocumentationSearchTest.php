<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests how each documentation version gets its own search index and search page.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationSearchIndex::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class VersionedDocumentationSearchTest extends VersionedDocumentationTestCase
{
    public function testEachVersionGetsItsOwnSearchIndexAndSearchPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $routes = Hyde::routes()->keys()->all();

        $this->assertContains('docs/1.x/search.json', $routes);
        $this->assertContains('docs/2.x/search.json', $routes);
        $this->assertContains('docs/1.x/search', $routes);
        $this->assertContains('docs/2.x/search', $routes);

        $this->assertNotContains('docs/search.json', $routes);
        $this->assertNotContains('docs/search', $routes);
    }

    public function testSearchIndexesOnlyContainPagesFromTheirVersion()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md', "# Installing 1.x\nLegacy");
        $this->file('_docs/2.x/installation.md', "# Installing 2.x\nCurrent");
        $this->file('_docs/2.x/upgrading.md', "# Upgrading\nUpgrade guide");

        $this->rediscoverPages();

        $one = json_decode($this->searchIndex('1.x')->compile(), true);
        $two = json_decode($this->searchIndex('2.x')->compile(), true);

        $this->assertSame(['Installing 1.x'], array_column($one, 'title'));
        $this->assertSame(['Installing 2.x', 'Upgrading'], array_column($two, 'title'));

        $this->assertSame('installation.html', $one[0]['destination']);
    }

    public function testVersionAgnosticSearchExclusionsApplyToAllVersions()
    {
        $this->enableVersions();

        config(['docs.exclude_from_search' => ['changelog']]);

        $this->file('_docs/1.x/changelog.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/changelog.md');

        $this->rediscoverPages();

        $this->assertSame(['Installation'], array_column(json_decode($this->searchIndex('1.x')->compile(), true), 'title'));
        $this->assertSame([], json_decode($this->searchIndex('2.x')->compile(), true));
    }

    public function testSearchIndexPathIsResolvedFromTheRenderedPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');

        $this->rediscoverPages();

        Render::setPage(DocumentationPage::get('1.x/installation'));

        $this->assertSame('docs/1.x/search.json', DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }

    public function testVersionedSearchPageCanBeOverriddenByUserPage()
    {
        $this->enableVersions();

        $this->file('_pages/docs/1.x/search.blade.php');
        $this->file('_docs/1.x/installation.md');

        $this->rediscoverPages();

        $this->assertInstanceOf(BladePage::class, Hyde::routes()->get('docs/1.x/search')->getPage());
        $this->assertNotInstanceOf(BladePage::class, Hyde::routes()->get('docs/2.x/search')->getPage());
    }

    protected function searchIndex(string $version): DocumentationSearchIndex
    {
        return Hyde::pages()->get("docs/$version/search.json");
    }
}
