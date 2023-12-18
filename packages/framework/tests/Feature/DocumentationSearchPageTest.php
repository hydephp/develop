<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Documentation\DocumentationSearchPage
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSearchCommandTest
 */
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

    public function testStaticRouteKeyHelperWithCustomOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');
        $this->assertSame('foo/search', DocumentationSearchPage::routeKey());
    }
}
