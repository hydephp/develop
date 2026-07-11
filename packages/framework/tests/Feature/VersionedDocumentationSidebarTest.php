<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Providers\NavigationServiceProvider;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

require_once __DIR__.'/VersionedDocumentationTestCase.php';

/**
 * Tests how documentation sidebars are generated and resolved for each documentation version.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(NavigationServiceProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class VersionedDocumentationSidebarTest extends VersionedDocumentationTestCase
{
    public function testEachVersionGetsItsOwnSidebar()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');
        $this->file('_docs/2.x/upgrading.md');

        $this->rediscoverPages();

        $this->assertSame('1.x', $this->sidebar('1.x')->version->name);
        $this->assertSame(['docs/1.x/installation'], $this->menuRouteKeys($this->sidebar('1.x')));

        $this->assertSame('2.x', $this->sidebar('2.x')->version->name);
        $this->assertSame(['docs/2.x/installation', 'docs/2.x/upgrading'], $this->menuRouteKeys($this->sidebar('2.x')));
    }

    public function testDefaultSidebarIsTheDefaultVersionSidebarWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $sidebar = $this->defaultSidebar();

        $this->assertSame('2.x', $sidebar->version->name);
        $this->assertSame(['docs/2.x/installation'], $this->menuRouteKeys($sidebar));

        // The default service resolves the default version's sidebar, instead of generating a second one.
        $this->assertSame($this->sidebar('2.x'), $sidebar);
    }

    public function testSidebarResolutionUsesTheVersionOfTheRenderedPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        Render::setPage(DocumentationPage::get('1.x/installation'));

        $this->assertSame('1.x', DocumentationSidebar::get()->version->name);

        Render::setPage(DocumentationPage::get('2.x/installation'));

        $this->assertSame('2.x', DocumentationSidebar::get()->version->name);
    }

    public function testSidebarGroupsSkipTheVersionSegment()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/getting-started/installation.md');
        $this->file('_docs/2.x/readme.md');

        $this->rediscoverPages();

        $this->assertSame('getting-started', DocumentationPage::get('2.x/getting-started/installation')->navigationMenuGroup());
        $this->assertNull(DocumentationPage::get('2.x/readme')->navigationMenuGroup());
    }

    public function testVersionAgnosticSidebarConfigurationAppliesToAllVersions()
    {
        $this->enableVersions();

        config(['docs.sidebar.order' => ['readme', 'installation']]);
        config(['docs.sidebar.labels' => ['readme' => 'Start Here']]);
        config(['docs.sidebar.exclude' => ['hidden-page']]);

        $this->file('_docs/1.x/readme.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/readme.md');
        $this->file('_docs/2.x/hidden-page.md');

        $this->rediscoverPages();

        $this->assertSame(500, DocumentationPage::get('1.x/readme')->navigationMenuPriority());
        $this->assertSame(501, DocumentationPage::get('1.x/installation')->navigationMenuPriority());
        $this->assertSame(500, DocumentationPage::get('2.x/readme')->navigationMenuPriority());

        $this->assertSame('Start Here', DocumentationPage::get('1.x/readme')->navigationMenuLabel());
        $this->assertSame('Start Here', DocumentationPage::get('2.x/readme')->navigationMenuLabel());

        $this->assertFalse(DocumentationPage::get('2.x/hidden-page')->showInNavigation());
    }

    public function testSidebarsExcludeTheirVersionIndexPage()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');

        $this->rediscoverPages();

        $this->assertSame(['docs/2.x/installation'], $this->menuRouteKeys($this->sidebar('2.x')));
    }

    public function testVersionSidebarFallsBackToIndexPageWhenItWouldOtherwiseBeEmpty()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');

        $this->rediscoverPages();

        $this->assertSame(['docs/1.x/index'], $this->menuRouteKeys($this->sidebar('1.x')));
    }

    public function testSidebarHomeRouteUsesTheSidebarVersion()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/index.md');

        $this->rediscoverPages();

        $this->assertSame('docs/1.x/index', $this->sidebar('1.x')->getHomeRoute()->getRouteKey());
    }
}
