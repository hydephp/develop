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
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Facades\Routes::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\Route::class)]
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

        Localization::usingLanguage('de', function (): void {
            // A logical reference to a page finds the page of the language being rendered.
            $this->assertSame('de/about', Routes::find('about')->getRouteKey());

            // While a key that already names its language keeps resolving to it.
            $this->assertSame('en/about', Routes::find('en/about')->getRouteKey());
        });
    }

    public function testCollisionBetweenALogicalRouteAndAPageWhoseIdentifierBeginsWithALanguageCode()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_pages/en/about.md', 'English Section', 'Body.');

        $this->withLanguages();

        Localization::usingLanguage('de', function (): void {
            // The unprefixed key resolves within the current language.
            $this->assertSame('de/about', Routes::find('about')->getRouteKey());

            // A key that already names a configured language is an explicit reference to
            // that language's route, not to `de/en/about`, which is a different page.
            $this->assertSame('en/about', Routes::find('en/about')->getRouteKey());
        });

        $this->assertSame('en/about', Routes::findExact('en/about')->getRouteKey());
        $this->assertSame('de/en/about', Routes::findExact('de/en/about')->getRouteKey());
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

    public function testRouteForLanguageReturnsAVariantPointingAtThatLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $canonical = new \Hyde\Support\Models\Route(Hyde::pages()->getPage('_pages/about.md'));

        $variant = $canonical->forLanguage('de');

        $this->assertSame('de/about', $variant->getRouteKey());
        $this->assertSame('about', $canonical->getRouteKey());
        $this->assertSame('de', $variant->getPage()->getLanguage());
        $this->assertSame($canonical::class, $variant::class);
    }

    public function testRoutesForLanguageReturnsOnlyTheRoutesOfThatLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $this->assertSame(['de/about'], Routes::forLanguage('de')
            ->filter(fn ($route): bool => $route->getPage() instanceof \Hyde\Pages\MarkdownPage)
            ->keys()->all());
    }

    public function testRoutesForLanguageNullReturnsTheNeutralRoutesNotLocalizedContent()
    {
        $this->withSiteUrl();
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $keys = Routes::forLanguage(null)->keys()->sort()->values()->all();

        $this->assertSame(['index', 'sitemap.xml'], $keys);
        $this->assertNotContains('en/about', $keys);
        $this->assertNotContains('de/about', $keys);
    }

    public function testRoutesForCurrentLanguageFollowsTheLanguageBeingRendered()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_pages/contact.md', 'Contact', 'Body.');

        $this->withLanguages();

        Localization::usingLanguage('de', function (): void {
            $this->assertSame(['de/about', 'de/contact'], Routes::forCurrentLanguage()
                ->filter(fn ($route): bool => $route->getPage() instanceof \Hyde\Pages\MarkdownPage)
                ->keys()->sort()->values()->all());
        });
    }

    public function testRoutesForCurrentLanguageBehavesSensiblyWhenLocalizationIsDisabled()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages([]);

        $this->assertSame(Routes::all()->keys()->sort()->values()->all(), Routes::forCurrentLanguage()
            ->keys()->sort()->values()->all());
    }

    public function testRouteReportsTheContentSourcePathOfItsPage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $this->assertSame('_locales/de/_pages/about.md', Routes::get('de/about')->getContentSourcePath());
        $this->assertSame('_pages/about.md', Routes::get('en/about')->getContentSourcePath());
    }

    public function testSiteWideArtifactsAreNotCompiledPerLanguage()
    {
        $this->withSiteUrl();

        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        // The sitemap describes the whole site, so there is one of it, in the webroot.
        $this->assertSame('sitemap.xml', Routes::findExact('sitemap.xml')->getOutputPath());

        $this->assertNull(Routes::findExact('en/sitemap.xml'));
        $this->assertNull(Routes::findExact('de/sitemap.xml'));
    }

    public function testPagesPresentingContentAreCompiledPerLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $this->assertTrue(Routes::get('en/about')->getPage()->isLocalizable());
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
