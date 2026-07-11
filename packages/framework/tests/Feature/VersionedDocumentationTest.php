<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
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

    /** @return array<string> The route keys of the pages linked by the menu items. */
    protected function menuRouteKeys(\Hyde\Framework\Features\Navigation\NavigationMenu $menu): array
    {
        return $menu->getItems()->map(function ($item): ?string {
            return $item instanceof \Hyde\Framework\Features\Navigation\NavigationItem ? $item->getPage()?->getRouteKey() : null;
        })->filter()->sort()->values()->all();
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

    // Section: Sidebars

    public function testEachVersionGetsItsOwnSidebar()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');
        $this->file('_docs/2.x/upgrading.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var DocumentationSidebar $oneSidebar */
        $oneSidebar = app('navigation.sidebar.1.x');
        /** @var DocumentationSidebar $twoSidebar */
        $twoSidebar = app('navigation.sidebar.2.x');

        $this->assertSame('1.x', $oneSidebar->version->name);
        $this->assertSame(['docs/1.x/installation'], $this->menuRouteKeys($oneSidebar));

        $this->assertSame('2.x', $twoSidebar->version->name);
        $this->assertSame(['docs/2.x/installation', 'docs/2.x/upgrading'], $this->menuRouteKeys($twoSidebar));
    }

    public function testDefaultSidebarIsTheDefaultVersionSidebarWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var DocumentationSidebar $sidebar */
        $sidebar = app('navigation.sidebar');

        $this->assertSame('2.x', $sidebar->version->name);
        $this->assertSame(['docs/2.x/installation'], $this->menuRouteKeys($sidebar));
    }

    public function testSidebarResolutionUsesTheVersionOfTheRenderedPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

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

        Hyde::boot(); // Reboot to rediscover new pages

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

        Hyde::boot(); // Reboot to rediscover new pages

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

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var DocumentationSidebar $sidebar */
        $sidebar = app('navigation.sidebar.2.x');

        $this->assertSame(['docs/2.x/installation'], $this->menuRouteKeys($sidebar));
    }

    public function testSidebarHomeRouteUsesTheSidebarVersion()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var DocumentationSidebar $sidebar */
        $sidebar = app('navigation.sidebar.1.x');

        $this->assertSame('docs/1.x/index', $sidebar->getHomeRoute()->getRouteKey());
    }

    // Section: Main navigation

    public function testMainNavigationOnlyShowsTheDefaultVersionDocumentationPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/index.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var MainNavigationMenu $menu */
        $menu = app('navigation.main');

        $keys = $this->menuRouteKeys($menu);

        $this->assertContains('docs/2.x/index', $keys);
        $this->assertNotContains('docs/1.x/index', $keys);
        $this->assertNotContains('docs/1.x/installation', $keys);
        $this->assertNotContains('docs/2.x/installation', $keys);
    }

    public function testMainNavigationDocumentationLinkGetsTheDocsLabel()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var MainNavigationMenu $menu */
        $menu = app('navigation.main');

        $item = $menu->getItems()->first(function ($item): bool {
            return $item instanceof \Hyde\Framework\Features\Navigation\NavigationItem && $item->getPage()?->getRouteKey() === 'docs/2.x/index';
        });

        $this->assertNotNull($item);
        $this->assertSame('Docs', $item->getLabel());
    }
}
