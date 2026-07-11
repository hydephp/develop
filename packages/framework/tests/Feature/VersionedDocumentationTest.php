<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\BuildWarnings;
use Hyde\Support\Facades\Render;
use Hyde\Framework\Exceptions\BuildWarning;
use Hyde\Foundation\Providers\NavigationServiceProvider;
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
#[\PHPUnit\Framework\Attributes\CoversClass(HydeCoreExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(NavigationServiceProvider::class)]
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

    public function testFlattenedRouteKeysAreUnchangedWhenVersioningIsDisabled()
    {
        $page = new DocumentationPage('getting-started/installation');

        $this->assertSame('docs/installation', $page->getRouteKey());
        $this->assertSame('docs/installation.html', $page->getOutputPath());
    }

    // Section: Home routes

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

        Hyde::boot(); // Reboot to rediscover new pages

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

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertSame('docs/1.x/index', DocumentationVersions::default()->homeRouteName());

        /** @var DocumentationSidebar $sidebar */
        $sidebar = app('navigation.sidebar');
        $this->assertSame('1.x', $sidebar->version->name);
        $this->assertSame(['docs/1.x/installation'], $this->menuRouteKeys($sidebar));

        /** @var MainNavigationMenu $menu */
        $menu = app('navigation.main');
        $keys = $this->menuRouteKeys($menu);

        $this->assertContains('docs/1.x/index', $keys);
        $this->assertNotContains('docs/2.x/index', $keys);
        $this->assertSame('1.x/index.html', Hyde::routes()->get('docs/index')->getPage()->destination);
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

    public function testUnversionedDocumentationFilesAreIgnoredWhenVersioningIsEnabled()
    {
        $this->enableVersions();

        $this->file('_docs/index.md');
        $this->file('_docs/shared.md');
        $this->file('_docs/getting-started/installation.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

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

        Hyde::boot(); // Reboot to rediscover new pages

        $warnings = array_map(fn (BuildWarning $warning): string => $warning->getMessage(), BuildWarnings::getWarnings());

        $this->assertSame(['Ignoring unversioned documentation file "_docs/installation.md" as documentation versioning is enabled. Move it into a registered version directory to include it in the site.'], $warnings);
    }

    public function testNoBuildWarningsAreReportedWhenAllDocumentationFilesAreVersioned()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertFalse(BuildWarnings::hasWarnings());
    }

    public function testUnversionedDocumentationFilesAreDiscoveredWhenVersioningIsDisabled()
    {
        $this->file('_docs/shared.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertContains('docs/shared', Hyde::routes()->getRoutes(DocumentationPage::class)->keys()->all());
    }

    public function testVersionedDocumentationUsesCustomDocumentationOutputDirectory()
    {
        $this->enableVersions();
        DocumentationPage::setOutputDirectory('reference');

        try {
            $this->file('_docs/2.x/index.md');
            $this->file('_docs/2.x/installation.md');

            Hyde::boot(); // Reboot to rediscover new pages

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

        // The default service resolves the default version's sidebar, instead of generating a second one.
        $this->assertSame(app('navigation.sidebar.2.x'), $sidebar);
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

    public function testVersionSidebarFallsBackToIndexPageWhenItWouldOtherwiseBeEmpty()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var DocumentationSidebar $sidebar */
        $sidebar = app('navigation.sidebar.1.x');

        $this->assertSame(['docs/1.x/index'], $this->menuRouteKeys($sidebar));
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

    // Section: Search

    public function testEachVersionGetsItsOwnSearchIndexAndSearchPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $routes = Hyde::routes()->keys()->all();

        $this->assertContains('docs/1.x/search.json', $routes);
        $this->assertContains('docs/2.x/search.json', $routes);
        $this->assertContains('docs/1.x/search', $routes);
        $this->assertContains('docs/2.x/search', $routes);

        $this->assertNotContains('docs/search.json', $routes);
        $this->assertNotContains('docs/search', $routes);
    }

    public function testSearchIndexesOnlyContainPagesFromTheirVersion()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md', "# Installing 1.x\nLegacy");
        $this->file('_docs/2.x/installation.md', "# Installing 2.x\nCurrent");
        $this->file('_docs/2.x/upgrading.md', "# Upgrading\nUpgrade guide");

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var \Hyde\Framework\Features\Documentation\DocumentationSearchIndex $oneIndex */
        $oneIndex = Hyde::pages()->get('docs/1.x/search.json');
        /** @var \Hyde\Framework\Features\Documentation\DocumentationSearchIndex $twoIndex */
        $twoIndex = Hyde::pages()->get('docs/2.x/search.json');

        $one = json_decode($oneIndex->compile(), true);
        $two = json_decode($twoIndex->compile(), true);

        $this->assertSame(['Installing 1.x'], array_column($one, 'title'));
        $this->assertSame(['Installing 2.x', 'Upgrading'], array_column($two, 'title'));

        $this->assertSame('installation.html', $one[0]['destination']);
    }

    public function testVersionAgnosticSearchExclusionsApplyToAllVersions()
    {
        $this->enableVersions();

        config(['docs.exclude_from_search' => ['changelog']]);

        $this->file('_docs/1.x/changelog.md');
        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/changelog.md');

        Hyde::boot(); // Reboot to rediscover new pages

        /** @var \Hyde\Framework\Features\Documentation\DocumentationSearchIndex $oneIndex */
        $oneIndex = Hyde::pages()->get('docs/1.x/search.json');
        /** @var \Hyde\Framework\Features\Documentation\DocumentationSearchIndex $twoIndex */
        $twoIndex = Hyde::pages()->get('docs/2.x/search.json');

        $this->assertSame(['Installation'], array_column(json_decode($oneIndex->compile(), true), 'title'));
        $this->assertSame([], json_decode($twoIndex->compile(), true));
    }

    public function testSearchIndexPathIsResolvedFromTheRenderedPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        Render::setPage(DocumentationPage::get('1.x/installation'));

        $this->assertSame('docs/1.x/search.json', \Hyde\Framework\Features\Documentation\DocumentationSearchIndex::routeKey(DocumentationVersions::current()));
    }

    public function testVersionedSearchPageCanBeOverriddenByUserPage()
    {
        $this->enableVersions();

        $this->file('_pages/docs/1.x/search.blade.php');
        $this->file('_docs/1.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertInstanceOf(\Hyde\Pages\BladePage::class, Hyde::routes()->get('docs/1.x/search')->getPage());
        $this->assertNotInstanceOf(\Hyde\Pages\BladePage::class, Hyde::routes()->get('docs/2.x/search')->getPage());
    }

    // Section: Documentation root redirect

    public function testDocumentationRootRedirectsToTheDefaultVersion()
    {
        $this->enableVersions();

        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $page = Hyde::routes()->get('docs/index')->getPage();

        $this->assertInstanceOf(\Hyde\Support\Models\Redirect::class, $page);
        $this->assertSame('2.x/index.html', $page->destination);
        $this->assertStringContainsString('http-equiv="refresh" content="0;url=\'2.x/index.html\'"', $page->compile());
        $this->assertFalse($page->showInNavigation());
    }

    public function testDocumentationRootRedirectUsesPrettyUrlsWhenEnabled()
    {
        $this->enableVersions();

        config(['hyde.pretty_urls' => true]);

        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertSame('2.x/', Hyde::routes()->get('docs/index')->getPage()->destination);
    }

    public function testDocumentationRootRedirectIsNotAddedWhenVersioningIsDisabled()
    {
        $this->file('_docs/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertInstanceOf(DocumentationPage::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectCanBeOverriddenByUserPage()
    {
        $this->enableVersions();

        $this->file('_pages/docs/index.md');
        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertInstanceOf(\Hyde\Pages\MarkdownPage::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectIsNotOverriddenByUnversionedDocumentationIndexPage()
    {
        $this->enableVersions();

        // Documentation pages outside the version directories are ignored, so this file is not a page.
        $this->file('_docs/index.md');
        $this->file('_docs/2.x/index.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertInstanceOf(\Hyde\Support\Models\Redirect::class, Hyde::routes()->get('docs/index')->getPage());
    }

    public function testDocumentationRootRedirectIsNotAddedWhenTheDefaultVersionHasNoIndexPage()
    {
        $this->enableVersions();

        $this->file('_docs/1.x/index.md');
        $this->file('_docs/2.x/installation.md');

        Hyde::boot(); // Reboot to rediscover new pages

        $this->assertNull(Hyde::routes()->get('docs/index'));
    }
}
