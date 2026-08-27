<?php

declare(strict_types=1);

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Desilva\Microserve\Request;
use Hyde\Testing\LocalizesSites;
use Hyde\RealtimeCompiler\Routing\PageRouter;

/**
 * Tests that serving a localized site resolves the same URLs as building it does, so that
 * what is previewed during development is what the built site will actually serve.
 *
 * The page router is exercised directly rather than through the HTTP kernel, as the kernel
 * bootstraps the application again, which would reload the configuration from disk and
 * discard the languages the test configures.
 */
class LocalizedServingTest extends TestCase
{
    use LocalizesSites;

    public static function setUpBeforeClass(): void
    {
        // The live edit toolbar starts a session, which a test process cannot do repeatedly.
        putenv('SERVER_LIVE_EDIT=false');
    }

    public static function tearDownAfterClass(): void
    {
        putenv('SERVER_LIVE_EDIT');
    }

    protected array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;

        $this->withoutDefaultPages();
        $this->withoutDocumentationSearch();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        $this->restoreDefaultPages();
        $this->restoreDocumentationSearch();

        parent::tearDown();
    }

    protected function requestFor(string $path): Request
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;

        return new Request();
    }

    protected function serve(string $path): string
    {
        return PageRouter::handle($this->requestFor($path))->body;
    }

    protected function isServed(string $path): bool
    {
        return PageRouter::hasRoute($this->requestFor($path));
    }

    public function testEachLanguageIsServedItsOwnContent()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');
        $this->localizedSource('de', '_pages/about.md', 'Impressum', 'Deutscher Text.');

        $this->withLanguages();

        $this->assertStringContainsString('Canonical body.', $this->serve('/en/about.html'));
        $this->assertStringContainsString('Deutscher Text.', $this->serve('/de/about.html'));
    }

    public function testEachLanguageIsServedItsOwnNavigation()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_pages/contact.md', 'Contact', 'Body.');

        $this->withLanguages();

        $html = $this->serve('/de/about.html');

        $this->assertStringContainsString('de/contact.html', $html);
        $this->assertStringNotContainsString('en/contact.html', $html);
    }

    public function testTheWebrootServesTheRedirectToTheDefaultLanguage()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        // The build writes a redirect to _site/index.html, so serving must not
        // resolve the webroot to the homepage of the default language instead.
        $this->assertStringContainsString("url='en/'", $this->serve('/'));
    }

    public function testUnprefixedPathsAreNotServedOnALocalizedSite()
    {
        $this->source('_pages/about.md', 'About', 'Body.');

        $this->withLanguages();

        // The build emits no _site/about.html, so serving it would preview a page
        // that the built site does not have at that path.
        $this->assertFalse($this->isServed('/about.html'));
    }

    public function testServingResolvesEveryPathThatBuildingEmits()
    {
        $this->source('_pages/about.md', 'About', 'Body.');
        $this->source('_docs/index.md', 'Docs', 'Body.');

        $this->withLanguages();

        foreach (Hyde::routes() as $route) {
            $this->assertTrue($this->isServed('/'.$route->getOutputPath()),
                "Route [{$route->getRouteKey()}] is emitted by the build but is not served.");
        }
    }

    public function testServingIsUnaffectedWhenLocalizationIsDisabled()
    {
        $this->source('_pages/about.md', 'About', 'Canonical body.');

        $this->withLanguages([]);

        $this->assertTrue($this->isServed('/about.html'));
        $this->assertStringContainsString('Canonical body.', $this->serve('/about.html'));
    }
}
