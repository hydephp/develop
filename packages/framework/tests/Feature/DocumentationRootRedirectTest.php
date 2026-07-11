<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Redirect;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests the generated documentation root page that redirects to the default version.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class DocumentationRootRedirectTest extends VersionedDocumentationTestCase
{
    public function testDocumentationRootRedirectsToTheDefaultVersion()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $page = Hyde::routes()->get('docs/index')->getPage();

        $this->assertInstanceOf(Redirect::class, $page);
        $this->assertSame('2.x/index.html', $page->destination);
        $this->assertStringContainsString('http-equiv="refresh" content="0;url=\'2.x/index.html\'"', $page->compile());
        $this->assertFalse($page->showInNavigation());
    }

    public function testDocumentationRootRedirectUsesPrettyUrlsWhenEnabled()
    {
        $this->enableVersions();

        config(['hyde.pretty_urls' => true]);

        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $this->assertSame('2.x/', Hyde::routes()->get('docs/index')->getPage()->destination);
    }

    public function testDocumentationRootRedirectIsNotAddedWhenVersioningIsDisabled()
    {
        $this->file('_docs/index.md');

        $this->rediscoverPages();

        $this->assertInstanceOf(DocumentationPage::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectCanBeOverriddenByUserPage()
    {
        $this->enableVersions();

        $this->file('_pages/docs/index.md');
        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $this->assertInstanceOf(MarkdownPage::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectIsNotOverriddenByUnversionedDocumentationIndexPage()
    {
        $this->enableVersions();

        // Documentation pages outside the version directories are ignored, so this file is not a page.
        $this->file('_docs/index.md');
        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $this->assertInstanceOf(Redirect::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectIsNotAddedWhenTheDefaultVersionHasNoIndexPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $this->assertNull(Hyde::routes()->get('docs/index'));
    }
}
