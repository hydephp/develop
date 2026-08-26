<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Facades\Localization;
use Hyde\Testing\LocalizesSites;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/**
 * Tests what a localized site renders: that each language gets its own navigation, that the
 * languages of a page are linked to each other, and that the page declares its language.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Localization::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Navigation\NavigationMenuGenerator::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Metadata\PageMetadataBag::class)]
class LocalizedRenderingTest extends TestCase
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

    /** Render a route the way the static site builder and the realtime compiler both do. */
    protected function render(string $routeKey): string
    {
        return Hyde::renderPage(Routes::get($routeKey)->getPage());
    }

    public function testNavigationMenusOnlyListTheRoutesOfTheirOwnLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_pages/contact.md', 'Contact', 'Body.');

        $this->withLanguages();

        Localization::usingLanguage('de', function (): void {
            $this->assertSame(['de/about', 'de/contact'], $this->menuRouteKeys(MainNavigationMenu::get()));
        });

        Localization::usingLanguage('en', function (): void {
            $this->assertSame(['en/about', 'en/contact'], $this->menuRouteKeys(MainNavigationMenu::get()));
        });
    }

    public function testRenderedNavigationDoesNotLinkToAnotherLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_pages/contact.md', 'Contact', 'Body.');

        $this->withLanguages();

        $html = $this->render('de/about');

        $this->assertStringContainsString('de/contact.html', $html);
        $this->assertStringNotContainsString('en/contact.html', $html);
    }

    public function testThePageDeclaresTheLanguageItWasCompiledFor()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $this->assertStringContainsString('<html lang="de"', $this->render('de/about'));
        $this->assertStringContainsString('<html lang="en"', $this->render('en/about'));
    }

    public function testEachLanguageDeclaresItsOwnCanonicalUrl()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withSiteUrl();
        $this->withLanguages();

        $this->assertStringContainsString('rel="canonical" href="https://example.com/de/about.html"', $this->render('de/about'));
        $this->assertStringContainsString('rel="canonical" href="https://example.com/en/about.html"', $this->render('en/about'));
    }

    public function testThePageLinksToItsOtherLanguagesWithHreflang()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withSiteUrl();
        $this->withLanguages();

        $html = $this->render('de/about');

        $this->assertStringContainsString('<link rel="alternate" href="https://example.com/en/about.html" hreflang="en">', $html);
        $this->assertStringContainsString('<link rel="alternate" href="https://example.com/de/about.html" hreflang="de">', $html);

        // The x-default alternate points at the default-language version of this same
        // page, not the site webroot, which only redirects to the default language.
        $this->assertStringContainsString('<link rel="alternate" href="https://example.com/en/about.html" hreflang="x-default">', $html);
    }

    public function testTheLanguageSwitcherLinksToTheSamePageInTheOtherLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $html = $this->render('de/about');

        $this->assertStringContainsString('id="language-switcher"', $html);
        $this->assertStringContainsString('href="../en/about.html"', $html);
    }

    public function testTheLanguageSwitcherShowsConfiguredDisplayNames()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages(['en' => 'English', 'de' => 'Deutsch']);

        $html = $this->render('de/about');

        $this->assertStringContainsString('English', $html);
        $this->assertStringContainsString('Deutsch', $html);
    }

    public function testAPageFallingBackToTheCanonicalSourceIsStillFullyLocalized()
    {
        // The page has no companion source, so only its content falls back.
        $this->source('_pages/contact.md', 'Contact', 'Canonical body.');
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withSiteUrl();
        $this->withLanguages();

        $html = $this->render('de/contact');

        $this->assertStringContainsString('Canonical body.', $html);
        $this->assertStringContainsString('<html lang="de"', $html);
        $this->assertStringContainsString('rel="canonical" href="https://example.com/de/contact.html"', $html);
        $this->assertStringContainsString('href="../en/contact.html"', $html);

        // Its navigation is the German one, listing the German page of every other page.
        $this->assertStringContainsString('de/about.html', $html);
        $this->assertStringNotContainsString('en/about.html', $html);
    }

    public function testTheDocumentationSidebarOnlyListsTheRoutesOfItsOwnLanguage()
    {
        $this->source('_docs/index.md', 'Docs', 'Body.');
        $this->source('_docs/install.md', 'Install', 'Body.');

        $this->withLanguages();

        Localization::usingLanguage('de', function (): void {
            $this->assertSame(['de/docs/index', 'de/docs/install'], $this->menuRouteKeys(DocumentationSidebar::get()));
        });

        Localization::usingLanguage('en', function (): void {
            $this->assertSame(['en/docs/index', 'en/docs/install'], $this->menuRouteKeys(DocumentationSidebar::get()));
        });
    }

    public function testDocumentationPagesGetALanguageSwitcherToo()
    {
        $this->source('_docs/index.md', 'Docs', 'Body.');
        $this->source('_docs/install.md', 'Install', 'Body.');

        $this->withLanguages();

        $html = $this->render('de/docs/install');

        $this->assertStringContainsString('id="language-switcher"', $html);
        $this->assertStringContainsString('href="../../en/docs/install.html"', $html);
        $this->assertStringNotContainsString('en/docs/index.html', $html);
    }

    public function testTheRenderedLocaleIsRestoredAfterwards()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        $locale = app()->getLocale();

        $this->render('de/about');

        $this->assertSame($locale, app()->getLocale());
    }

    public function testDisablingLocalizationRendersNoLocalizationMarkup()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withSiteUrl();
        $this->withLanguages([]);

        $html = $this->render('about');

        $this->assertStringNotContainsString('id="language-switcher"', $html);
        $this->assertStringNotContainsString('hreflang', $html);
        $this->assertStringContainsString('rel="canonical" href="https://example.com/about.html"', $html);
    }
}
