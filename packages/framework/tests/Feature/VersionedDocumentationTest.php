<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

/**
 * High level feature test for the versioned documentation pages feature.
 *
 * @see \Hyde\Framework\Testing\Feature\DocumentationVersionsTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersion::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class VersionedDocumentationTest extends TestCase
{
    protected function enableVersions(): void
    {
        config(['docs.versions' => ['1.x', '2.x']]);
    }

    // Section: Page version resolution

    public function testPageVersionIsNullWhenVersioningIsDisabled()
    {
        $this->assertNull((new DocumentationPage('1.x/installation'))->getDocumentationVersion());
    }

    public function testPageVersionIsNullForPagesOutsideVersionDirectories()
    {
        $this->enableVersions();

        $this->assertNull((new DocumentationPage('installation'))->getDocumentationVersion());
        $this->assertNull((new DocumentationPage('getting-started/installation'))->getDocumentationVersion());
    }

    public function testPageVersionIsResolvedFromIdentifierPrefix()
    {
        $this->enableVersions();

        $this->assertSame('1.x', (new DocumentationPage('1.x/installation'))->getDocumentationVersion()->name);
        $this->assertSame('2.x', (new DocumentationPage('2.x/getting-started/installation'))->getDocumentationVersion()->name);
    }

    // Section: Route keys and output paths

    public function testFlattenedRouteKeysKeepVersionPrefix()
    {
        $this->enableVersions();

        $page = new DocumentationPage('2.x/getting-started/installation');

        $this->assertSame('docs/2.x/installation', $page->getRouteKey());
        $this->assertSame('docs/2.x/installation.html', $page->getOutputPath());
    }

    public function testFlattenedRouteKeysForTopLevelVersionPages()
    {
        $this->enableVersions();

        $page = new DocumentationPage('1.x/installation');

        $this->assertSame('docs/1.x/installation', $page->getRouteKey());
        $this->assertSame('docs/1.x/installation.html', $page->getOutputPath());
    }

    public function testFlattenedRouteKeysStripNumericalPrefixesWithinVersions()
    {
        $this->enableVersions();

        $page = new DocumentationPage('2.x/01-installation');

        $this->assertSame('docs/2.x/installation', $page->getRouteKey());
    }

    public function testNonFlattenedRouteKeysAreUnchanged()
    {
        $this->enableVersions();

        config(['docs.flattened_output_paths' => false]);

        $page = new DocumentationPage('2.x/getting-started/installation');

        $this->assertSame('docs/2.x/getting-started/installation', $page->getRouteKey());
        $this->assertSame('docs/2.x/getting-started/installation.html', $page->getOutputPath());
    }

    public function testUnversionedPagesFlattenAsBeforeWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $page = new DocumentationPage('getting-started/installation');

        $this->assertSame('docs/installation', $page->getRouteKey());
        $this->assertSame('docs/installation.html', $page->getOutputPath());
    }

    public function testFlattenedRouteKeysAreUnchangedWhenVersioningIsDisabled()
    {
        $page = new DocumentationPage('getting-started/installation');

        $this->assertSame('docs/installation', $page->getRouteKey());
        $this->assertSame('docs/installation.html', $page->getOutputPath());
    }

    // Section: Home routes

    public function testHomeRouteNameUsesDefaultVersionWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $this->assertSame('docs/2.x/index', DocumentationPage::homeRouteName());
    }

    public function testHomeRouteNameAcceptsExplicitVersion()
    {
        $this->enableVersions();

        $this->assertSame('docs/1.x/index', DocumentationPage::homeRouteName('1.x'));
        $this->assertSame('docs/1.x/index', DocumentationPage::homeRouteName(DocumentationVersions::get('1.x')));
    }

    public function testHomeRouteNameIsUnchangedWhenVersioningIsDisabled()
    {
        $this->assertSame('docs/index', DocumentationPage::homeRouteName());
    }

    public function testHomeFindsVersionIndexRoutes()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertSame('docs/2.x/index', DocumentationPage::home()->getRouteKey());
        $this->assertSame('docs/1.x/index', DocumentationPage::home('1.x')->getRouteKey());
    }

    // Section: Discovery

    public function testVersionedPagesAreDiscoveredWithVersionedRouteKeys()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');
        $this->file('_docs/2.x/getting-started/advanced.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $routes = Hyde::routes()->getRoutes(DocumentationPage::class)->keys()->sort()->values()->all();

        $this->assertSame(['docs/1.x/installation', 'docs/2.x/advanced', 'docs/2.x/installation'], $routes);
    }
}
