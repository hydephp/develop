<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Facades\Localization;
use Hyde\Testing\LocalizesSites;
use Hyde\Foundation\Facades\Routes;

/**
 * Tests the content model of the site localization feature: how one authored page becomes
 * the pages and routes of each language, and where each one takes its content from.
 *
 * @see \Hyde\Facades\Localization
 */
#[\PHPUnit\Framework\Attributes\CoversClass(Localization::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Kernel\PageCollection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Kernel\RouteCollection::class)]
class LocalizedContentTest extends TestCase
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

    public function testSourceFileRemainsOnePageWhenLocalized()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $this->assertSame(['_pages/about.md'], Hyde::pages()->keys()->all());
    }

    public function testSourceFileFansOutIntoOneRoutePerLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');

        $this->withLanguages();

        $this->assertSame(['en/about', 'de/about'], Routes::all()
            ->filter(fn ($route): bool => $route->getPage() instanceof MarkdownPage)
            ->keys()->all());
    }

    public function testCompanionSourceSuppliesBothContentAndFrontMatter()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $english = Routes::get('en/about')->getPage();
        $german = Routes::get('de/about')->getPage();

        $this->assertSame('About', $english->title);
        $this->assertSame('Impressum', $german->title);

        $this->assertStringContainsString('Canonical body.', $english->markdown->body());
        $this->assertStringContainsString('Deutscher Text.', $german->markdown->body());
    }

    public function testPageWithoutCompanionSourceFallsBackToTheCanonicalContent()
    {
        $this->source('_pages/contact.md', 'Contact', 'Canonical body.');

        $this->withLanguages();

        $german = Routes::get('de/contact')->getPage();

        $this->assertSame('Contact', $german->title);
        $this->assertStringContainsString('Canonical body.', $german->markdown->body());

        // The content falls back, but the page is still compiled for its own language.
        $this->assertSame('de', $german->getLanguage());
    }

    public function testSourcePathStaysCanonicalWhileTheContentSourcePathReportsTheCompanion()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $german = Routes::get('de/about')->getPage();

        $this->assertSame('_pages/about.md', $german->getSourcePath());
        $this->assertSame('_locales/de/_pages/about.md', $german->getContentSourcePath());
    }

    public function testContentSourcePathIsTheCanonicalSourceWhenThereIsNoCompanion()
    {
        $this->source('_pages/contact.md', 'Contact', 'Canonical body.');

        $this->withLanguages();

        $german = Routes::get('de/contact')->getPage();

        $this->assertSame('_pages/contact.md', $german->getSourcePath());
        $this->assertSame('_pages/contact.md', $german->getContentSourcePath());
    }

    public function testCompanionSourcesAreNotDiscoveredAsPagesOfTheirOwn()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $this->assertCount(1, Hyde::pages());
        $this->assertNull(Routes::all()->get('_locales/de/_pages/about'));
    }

    public function testLanguagesCanBeConfiguredWithDisplayNames()
    {
        $this->withLanguages(['en' => 'English', 'de' => 'Deutsch']);

        $this->assertSame(['en', 'de'], Localization::languages());
        $this->assertSame('Deutsch', Localization::label('de'));
    }

    public function testLanguagesConfiguredAsAListUseTheirCodeAsTheirName()
    {
        $this->withLanguages(['en', 'de']);

        $this->assertSame(['en', 'de'], Localization::languages());
        $this->assertSame('de', Localization::label('de'));
    }

    public function testLocalizationIsDisabledWhenNoLanguagesAreConfigured()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');

        $this->withLanguages([]);

        $this->assertFalse(Localization::enabled());
        $this->assertNull(Localization::currentLanguage());

        $page = Hyde::pages()->getPage('_pages/about.md');

        $this->assertNull($page->getLanguage());
        $this->assertSame('about', $page->getRouteKey());
        $this->assertSame('about.html', $page->getOutputPath());
        $this->assertSame(['about'], Routes::all()->keys()->all());
    }
}
