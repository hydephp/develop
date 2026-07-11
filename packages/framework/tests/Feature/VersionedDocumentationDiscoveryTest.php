<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Support\BuildWarnings;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Exceptions\BuildWarning;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests how versioned documentation source files are discovered into pages and routes.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class VersionedDocumentationDiscoveryTest extends VersionedDocumentationTestCase
{
    public function testVersionedPagesAreDiscoveredWithVersionedRouteKeys()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');
        $this->file('_docs/2.x/getting-started/advanced.md');

        $this->rediscoverPages();

        $routes = Hyde::routes()->getRoutes(DocumentationPage::class)->keys()->sort()->values()->all();

        $this->assertSame(['docs/1.x/installation', 'docs/2.x/advanced', 'docs/2.x/installation'], $routes);
    }

    public function testUnversionedDocumentationFilesAreIgnoredWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $this->file('_docs/index.md');
        $this->file('_docs/shared.md');
        $this->file('_docs/getting-started/installation.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $routes = Hyde::routes()->getRoutes(DocumentationPage::class)->keys()->all();

        $this->assertSame(['docs/2.x/installation'], $routes);

        $this->assertEmpty(Hyde::files()->getFiles(DocumentationPage::class)->filter(function ($file): bool {
            return ! str_starts_with($file->getPath(), '_docs/2.x/');
        }));
    }

    public function testIgnoredUnversionedDocumentationFilesAreReportedAsBuildWarnings()
    {
        $this->enableVersions();

        $this->file('_docs/installation.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $warnings = array_map(fn (BuildWarning $warning): string => $warning->getMessage(), BuildWarnings::getWarnings());

        $this->assertSame(['Ignoring unversioned documentation file "_docs/installation.md" as documentation versioning is enabled. Move it into a registered version directory to include it in the site.'], $warnings);
    }

    public function testNoBuildWarningsAreReportedWhenAllDocumentationFilesAreVersioned()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $this->assertFalse(BuildWarnings::hasWarnings());
    }

    public function testUnversionedDocumentationFilesAreDiscoveredWhenVersioningIsDisabled()
    {
        $this->file('_docs/shared.md');

        $this->rediscoverPages();

        $this->assertContains('docs/shared', Hyde::routes()->getRoutes(DocumentationPage::class)->keys()->all());
    }

    public function testVersionedDocumentationUsesCustomDocumentationOutputDirectory()
    {
        $this->enableVersions();
        DocumentationPage::setOutputDirectory('reference');

        try {
            $this->file('_docs/2.x/index.md');
            $this->file('_docs/2.x/installation.md');

            $this->rediscoverPages();

            $routes = Hyde::routes()->keys()->all();

            $this->assertContains('reference/index', $routes);
            $this->assertContains('reference/2.x/index', $routes);
            $this->assertContains('reference/2.x/installation', $routes);
            $this->assertContains('reference/1.x/search.json', $routes);
            $this->assertContains('reference/2.x/search.json', $routes);
            $this->assertContains('reference/1.x/search', $routes);
            $this->assertContains('reference/2.x/search', $routes);
            $this->assertSame('2.x/index.html', Hyde::routes()->get('reference/index')->getPage()->destination);
        } finally {
            DocumentationPage::setOutputDirectory('docs');
        }
    }
}
