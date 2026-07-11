<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Foundation\Providers\NavigationServiceProvider;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests how versioned documentation pages are represented in the main navigation menu.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(NavigationServiceProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class VersionedDocumentationNavigationTest extends VersionedDocumentationTestCase
{
    public function testMainNavigationOnlyShowsTheDefaultVersionDocumentationPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $keys = $this->menuRouteKeys($this->mainNavigation());

        $this->assertContains('docs/2.x/index', $keys);
        $this->assertNotContains('docs/1.x/index', $keys);
        $this->assertNotContains('docs/1.x/installation', $keys);
        $this->assertNotContains('docs/2.x/installation', $keys);
    }

    public function testMainNavigationDocumentationLinkGetsTheDocsLabel()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $item = $this->mainNavigation()->getItems()->first(function ($item): bool {
            return $item instanceof NavigationItem && $item->getPage()?->getRouteKey() === 'docs/2.x/index';
        });

        $this->assertNotNull($item);
        $this->assertSame('Docs', $item->getLabel());
    }
}
