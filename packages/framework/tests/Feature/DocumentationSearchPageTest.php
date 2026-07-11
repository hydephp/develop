<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSearchCommandTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Documentation\DocumentationSearchPage::class)]
class DocumentationSearchPageTest extends TestCase
{
    public function testCanCreateDocumentationSearchPageInstance()
    {
        $this->assertInstanceOf(DocumentationSearchPage::class, new DocumentationSearchPage());
    }

    public function testRouteKeyIsSetToDocumentationOutputDirectory()
    {
        $page = new DocumentationSearchPage();
        $this->assertSame('docs/search', $page->routeKey);
    }

    public function testRouteKeyIsSetToConfiguredDocumentationOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');

        $page = new DocumentationSearchPage();
        $this->assertSame('foo/search', $page->routeKey);
    }

    public function testRouteKeyIsSetToVersionedDocumentationOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);

        $page = new DocumentationSearchPage(DocumentationVersions::get('1.x'));

        $this->assertSame('docs/1.x/search', $page->routeKey);
        $this->assertSame('1.x', $page->getDocumentationVersion()->name);
    }

    public function testEnabledDefaultsToTrue()
    {
        $this->assertTrue(DocumentationSearchPage::enabled());
    }

    public function testEnabledIsFalseWhenDisabled()
    {
        config(['docs.create_search_page' => false]);
        $this->assertFalse(DocumentationSearchPage::enabled());
    }

    public function testEnabledIsFalseWhenRouteExists()
    {
        Hyde::pages()->put('docs/search', new InMemoryPage('docs/search'));
        $this->assertFalse(DocumentationSearchPage::enabled());
    }

    public function testEnabledIsFalseWhenVersionedRouteExists()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::pages()->put('docs/1.x/search', new InMemoryPage('docs/1.x/search'));

        $this->assertFalse(DocumentationSearchPage::enabled(DocumentationVersions::get('1.x')));
        $this->assertTrue(DocumentationSearchPage::enabled(DocumentationVersions::get('2.x')));
    }

    public function testVersionedSearchPageIsDisabledWhenSearchPagesAreDisabled()
    {
        config(['docs.versions' => ['1.x'], 'docs.create_search_page' => false]);

        $this->assertFalse(DocumentationSearchPage::enabled(DocumentationVersions::get('1.x')));
    }

    public function testEnabledIsFalseWhenDisabledAndRouteExists()
    {
        config(['docs.create_search_page' => false]);
        Hyde::pages()->put('docs/search', new InMemoryPage('docs/search'));
        $this->assertFalse(DocumentationSearchPage::enabled());
    }

    public function testStaticRouteKeyHelper()
    {
        $this->assertSame('docs/search', DocumentationSearchPage::routeKey());
    }

    public function testStaticRouteKeyHelperWithVersion()
    {
        config(['docs.versions' => ['1.x']]);

        $this->assertSame('docs/1.x/search', DocumentationSearchPage::routeKey(DocumentationVersions::get('1.x')));
    }

    public function testStaticRouteKeyHelperWithCustomOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');
        $this->assertSame('foo/search', DocumentationSearchPage::routeKey());
    }

    public function testStaticRouteKeyHelperWithVersionAndCustomOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);
        DocumentationPage::setOutputDirectory('foo');

        $this->assertSame('foo/1.x/search', DocumentationSearchPage::routeKey(DocumentationVersions::get('1.x')));
    }

    public function testStaticRouteKeyHelperWithRootOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('');
        $this->assertSame('search', DocumentationSearchPage::routeKey());
    }

    public function testCanRenderSearchPage()
    {
        $page = new DocumentationSearchPage();

        Hyde::shareViewData($page);
        $this->assertStringContainsString('<h1>Search the HydePHP Documentation</h1>', $page->compile());
    }

    public function testRenderedSearchPageUsesDocumentationPageLayout()
    {
        $page = new DocumentationSearchPage();

        Hyde::shareViewData($page);
        $html = $page->compile();

        $this->assertStringContainsString('<body id="hyde-docs"', $html);
    }

    public function testRenderedSearchPageDoesNotUseSemanticDocumentationMarkup()
    {
        $page = new DocumentationSearchPage();

        Hyde::shareViewData($page);
        $html = $page->compile();

        $this->assertStringNotContainsString('<header id="document-header"', $html);
        $this->assertStringNotContainsString('<section id="document-main-content"', $html);
        $this->assertStringNotContainsString('<footer id="document-footer"', $html);
    }

    public function testRenderedSearchPageUsesCustomSidebarHeader()
    {
        config(['docs.sidebar.header' => 'My Project']);
        $page = new DocumentationSearchPage();

        Hyde::shareViewData($page);
        $this->assertStringContainsString('<h1>Search My Project</h1>', $page->compile());
    }

    public function testRenderedSearchPageExpandsDocsInSidebarHeader()
    {
        config(['docs.sidebar.header' => 'Custom Docs']);
        $page = new DocumentationSearchPage();

        Hyde::shareViewData($page);
        $this->assertStringContainsString('<h1>Search the Custom Documentation</h1>', $page->compile());
    }
}
