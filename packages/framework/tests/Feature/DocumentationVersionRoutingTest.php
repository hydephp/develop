<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests how documentation versions shape page route keys, output paths, and home routes.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersion::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class DocumentationVersionRoutingTest extends VersionedDocumentationTestCase
{
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

    public function testFlattenedRouteKeysAreUnchangedWhenVersioningIsDisabled()
    {
        $page = new DocumentationPage('getting-started/installation');

        $this->assertSame('docs/installation', $page->getRouteKey());
        $this->assertSame('docs/installation.html', $page->getOutputPath());
    }

    public function testDocumentationHomeRouteNameIsTheDocumentationRootRegardlessOfVersioning()
    {
        $this->assertSame('docs/index', DocumentationPage::homeRouteName());

        $this->enableVersions();

        $this->assertSame('docs/index', DocumentationPage::homeRouteName());
    }

    public function testVersionsOwnTheirHomeRoutes()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $this->assertSame('docs/1.x/index', DocumentationVersions::get('1.x')->homeRouteName());
        $this->assertSame('docs/1.x/index', DocumentationVersions::get('1.x')->home()->getRouteKey());

        // The documentation root is the generated redirect page pointing to the default version.
        $this->assertSame('docs/index', DocumentationPage::home()->getRouteKey());
    }

    public function testExplicitDefaultVersionIsUsedForVersionedDocumentationEntryPoints()
    {
        config(['docs.versions' => ['1.x', '2.x'], 'docs.default_version' => '1.x']);

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $this->assertSame('docs/1.x/index', DocumentationVersions::default()->homeRouteName());

        $sidebar = $this->defaultSidebar();

        $this->assertSame('1.x', $sidebar->version->name);
        $this->assertSame(['docs/1.x/installation'], $this->menuRouteKeys($sidebar));

        $keys = $this->menuRouteKeys($this->mainNavigation());

        $this->assertContains('docs/1.x/index', $keys);
        $this->assertNotContains('docs/2.x/index', $keys);
        $this->assertSame('1.x/index.html', Hyde::routes()->get('docs/index')->getPage()->destination);
    }
}
