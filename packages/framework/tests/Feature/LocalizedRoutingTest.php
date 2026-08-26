<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Facades\Localization;
use Hyde\Testing\LocalizesSites;
use Hyde\Support\Models\Redirect;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;

/**
 * Tests how a localized site is routed: how route keys are resolved, which files a build
 * emits, and how the destinations of redirects are resolved for each language.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Localization::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Kernel\RouteCollection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\Redirect::class)]
class LocalizedRoutingTest extends TestCase
{
    use LocalizesSites;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();
    }

    protected function tearDown(): void
    {
        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();

        parent::tearDown();
    }

    public function testRouteKeysAndOutputPathsArePrefixedWithTheirLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $page = Routes::get('de/about')->getPage();

        $this->assertSame('de/about', $page->getRouteKey());
        $this->assertSame('de/about.html', $page->getOutputPath());
    }

    public function testRouteLookupsResolveWithinTheLanguageBeingRendered()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        Hyde::shareViewData(Routes::get('de/about')->getPage());

        // A logical reference to a page finds the page of the language being rendered.
        $this->assertSame('de/about', Routes::find('about')->getRouteKey());

        // While a key that already names its language keeps resolving to it.
        $this->assertSame('en/about', Routes::find('en/about')->getRouteKey());
    }

    public function testExactRouteLookupsDoNotResolveWithinALanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        Hyde::shareViewData(Routes::get('de/about')->getPage());

        $this->assertNull(Routes::findExact('about'));
        $this->assertSame('de/about', Routes::findExact('de/about')->getRouteKey());
    }

    public function testTheWebrootRedirectsToTheDefaultLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $redirect = Routes::findExact('index')->getPage();

        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->assertSame('index.html', $redirect->getOutputPath());
        $this->assertStringContainsString("url='en/'", $redirect->compile());
    }

    public function testRedirectDestinationsNamingAPageStayInTheirOwnLanguage()
    {
        config(['hyde.redirects' => ['old-about' => 'about']]);

        $this->withLanguages();

        // Destinations are relative to the redirect, which already sits in its language.
        $this->assertStringContainsString("url='about'", Routes::get('en/old-about')->getPage()->compile());
        $this->assertStringContainsString("url='about'", Routes::get('de/old-about')->getPage()->compile());
    }

    public function testRedirectDestinationsNamingALanguageReachThatLanguage()
    {
        config(['hyde.redirects' => ['qualified' => 'en/about']]);

        $this->withLanguages();

        // Resolved from the webroot, so the destination is not sought within the current language.
        $this->assertStringContainsString("url='../en/about'", Routes::get('de/qualified')->getPage()->compile());
        $this->assertStringContainsString("url='../en/about'", Routes::get('en/qualified')->getPage()->compile());
    }

    public function testExternalRedirectDestinationsAreLeftAlone()
    {
        config(['hyde.redirects' => ['gh' => 'https://github.com/hydephp']]);

        $this->withLanguages();

        $this->assertStringContainsString("url='https://github.com/hydephp'", Routes::get('de/gh')->getPage()->compile());
    }

    public function testPagesAddedByExtensionsAreLocalizedAsWell()
    {
        $this->restoreDocumentationSearch();

        $this->source('_docs/index.md', 'Docs', 'Body.');

        $this->withLanguages();

        // The search index is added by the core extension, after the pages are discovered.
        $this->assertSame('en/docs/search.json', Routes::get('en/docs/search.json')->getOutputPath());
        $this->assertSame('de/docs/search.json', Routes::get('de/docs/search.json')->getOutputPath());
    }

    public function testSearchIndexesAreBuiltForEachLanguage()
    {
        $this->restoreDocumentationSearch();

        $this->source('_docs/index.md', 'Docs', 'Body.');

        $this->withLanguages();

        foreach (['en', 'de'] as $language) {
            $page = Routes::get("$language/docs/search.json")->getPage();

            $this->assertInstanceOf(DocumentationSearchIndex::class, $page);

            StaticPageBuilder::handle($page);

            $this->assertFileExists(Hyde::sitePath("$language/docs/search.json"));
        }
    }

    public function testBuildingASiteEmitsExactlyTheRoutesItHas()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        foreach (Routes::all() as $route) {
            StaticPageBuilder::handle($route->getPage());
        }

        // Every route is emitted where its key says, and nothing is emitted unprefixed.
        $this->assertFileExists(Hyde::sitePath('en/about.html'));
        $this->assertFileExists(Hyde::sitePath('de/about.html'));
        $this->assertFileExists(Hyde::sitePath('index.html'));
        $this->assertFileDoesNotExist(Hyde::sitePath('about.html'));
    }

    public function testDisablingLocalizationLeavesRoutingUntouched()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages([]);

        $this->assertSame(['about'], Routes::all()->keys()->all());
        $this->assertSame('about', Routes::find('about')->getRouteKey());
        $this->assertSame('about', Routes::findExact('about')->getRouteKey());
        $this->assertNull(Localization::currentLanguage());
    }
}
