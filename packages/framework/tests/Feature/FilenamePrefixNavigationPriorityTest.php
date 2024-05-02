<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * High level test for the feature that allows navigation items to be sorted by filename prefix.
 *
 * The feature can be disabled in the config. It also works within sidebar groups,
 * so that multiple groups can have the same prefix independent of other groups.
 *
 * @covers \Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Factories\NavigationDataFactory // Todo: Update the unit test for this class.
 * @covers \Hyde\Support\Models\RouteKey // Todo: Update the unit test for this class.
 *
 * @see \Hyde\Framework\Testing\Unit\FilenamePrefixNavigationPriorityUnitTest
 *
 * Todo: Add test to ensure explicitly set priority overrides filename prefix.
 */
class FilenamePrefixNavigationPriorityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = new FilenamePrefixNavigationPriorityTestingHelper($this);

        Config::set('hyde.navigation.subdirectories', 'dropdown');

        // Todo: Replace kernel with mock class
        $this->withoutDefaultPages();
    }

    protected function tearDown(): void
    {
        $this->restoreDefaultPages();

        parent::tearDown();
    }

    public function testSourceFilesHaveTheirNumericalPrefixTrimmedFromRouteKeys()
    {
        $this->file('_pages/01-home.md');

        $identifier = '01-home';

        // Assert it is discovered.
        $discovered = MarkdownPage::get($identifier);
        $this->assertNotNull($discovered, 'The page was not discovered.');

        // Assert it is parsable
        $parsed = MarkdownPage::parse($identifier);
        $this->assertNotNull($parsed, 'The page was not parsable.');

        // Sanity check
        $this->assertEquals($discovered, $parsed);

        $page = $discovered;

        // Assert identifier is the same.
        $this->assertSame($identifier, $page->getIdentifier());

        // Assert the route key is trimmed.
        $this->assertSame('home', $page->getRouteKey());

        // Assert route key dependents are trimmed.
        $this->assertSame('home.html', $page->getOutputPath());
    }

    public function testSourceFilesDoNotHaveTheirNumericalPrefixTrimmedFromRouteKeysWhenFeatureIsDisabled()
    {
        Config::set('hyde.filename_page_ordering', false);

        $this->file('_pages/01-home.md');

        $identifier = '01-home';

        // Assert it is discovered.
        $discovered = MarkdownPage::get($identifier);
        $this->assertNotNull($discovered, 'The page was not discovered.');

        // Assert it is parsable
        $parsed = MarkdownPage::parse($identifier);
        $this->assertNotNull($parsed, 'The page was not parsable.');

        // Sanity check
        $this->assertEquals($discovered, $parsed);

        $page = $discovered;

        // Assert identifier is the same.
        $this->assertSame($identifier, $page->getIdentifier());

        // Assert the route key is trimmed.
        $this->assertSame($identifier, $page->getRouteKey());

        // Assert route key dependents are trimmed.
        $this->assertSame("$identifier.html", $page->getOutputPath());
    }

    public function test_fixtureFlatMain_ordering()
    {
        $this->setUpFixture($this->fixtureFlatMain());

        $this->assertOrder(['home', 'about', 'contact']);
    }

    public function test_fixtureFlatMain_reverse_ordering()
    {
        // This is just a sanity check to make sure the helper is working, so we only need one of these.
        $this->setUpFixture(array_reverse($this->fixtureFlatMain()));

        $this->assertOrder(['home', 'about', 'contact']);
    }

    public function test_fixtureGroupedMain_ordering()
    {
        $this->setUpFixture($this->fixtureGroupedMain());

        $this->assertOrder(['home', 'about', 'contact', 'api' => [
            'readme', 'installation', 'getting-started',
        ]]);
    }

    public function test_fixtureGroupedMain_reverse_ordering()
    {
        // Also a sanity check but for the inner group as well.
        $this->setUpFixture($this->arrayReverseRecursive($this->fixtureGroupedMain()));

        $this->assertOrder(['home', 'about', 'contact', 'api' => [
            'readme', 'installation', 'getting-started',
        ]]);
    }

    public function test_fixtureFlatSidebar_ordering()
    {
        $this->setUpSidebarFixture($this->fixtureFlatSidebar());

        $this->assertSidebarOrder(['readme', 'installation', 'getting-started']);
    }

    public function test_fixtureGroupedSidebar_ordering()
    {
        $this->setUpSidebarFixture($this->fixtureGroupedSidebar());

        $this->assertSidebarOrder([
            'other' => ['readme', 'installation', 'getting-started'],
            'introduction' => ['general', 'resources', 'requirements'],
            'advanced' => ['features', 'extensions', 'configuration'],
        ]);
    }

    protected function setUpSidebarFixture(array $files): self
    {
        return $this->setUpFixture($files, sidebar: true);
    }

    protected function setUpFixture(array $files, bool $sidebar = false): self
    {
        $this->helper->setUpFixture($files, $sidebar);

        return $this;
    }

    protected function assertSidebarOrder(array $expected): void
    {
        $this->assertOrder($expected, sidebar: true);
    }

    /** @param array<string> $expected */
    protected function assertOrder(array $expected, bool $sidebar = false): void
    {
        $actual = $this->helper->createComparisonFormat($sidebar);

        $this->assertSame($expected, $actual);
    }

    protected function fixtureFlatMain(): array
    {
        return [
            '01-home.md',
            '02-about.md',
            '03-contact.md',
        ];
    }

    protected function fixtureGroupedMain(): array
    {
        return [
            '01-home.md',
            '02-about.md',
            '03-contact.md',
            '04-api' => [
                '01-readme.md',
                '02-installation.md',
                '03-getting-started.md',
            ],
        ];
    }

    protected function fixtureFlatSidebar(): array
    {
        return [
            '01-readme.md',
            '02-installation.md',
            '03-getting-started.md',
        ];
    }

    protected function fixtureGroupedSidebar(): array
    {
        return [
            '01-readme.md',
            '02-installation.md',
            '03-getting-started.md',
            '04-introduction' => [
                '01-general.md',
                '02-resources.md',
                '03-requirements.md',
            ],
            '05-advanced' => [
                '01-features.md',
                '02-extensions.md',
                '03-configuration.md',
            ],
        ];
    }

    protected function fixturePrefixSyntaxes(): array
    {
        return [
            [
                '1-foo.md',
                '2-bar.md',
                '3-baz.md',
            ], [
                '01-foo.md',
                '02-bar.md',
                '03-baz.md',
            ], [
                '001-foo.md',
                '002-bar.md',
                '003-baz.md',
            ],
        ];
    }

    protected function fixtureFileExtensions(): array
    {
        return [
            '01-foo.md',
            '02-bar.html',
            '03-baz.blade.php',
        ];
    }

    protected function arrayReverseRecursive(array $array): array
    {
        $reversed = array_reverse($array);

        foreach ($reversed as $key => $value) {
            if (is_array($value)) {
                $reversed[$key] = $this->arrayReverseRecursive($value);
            }
        }

        return $reversed;
    }
}

class FilenamePrefixNavigationPriorityTestingHelper
{
    protected FilenamePrefixNavigationPriorityTest $test;

    public function __construct(FilenamePrefixNavigationPriorityTest $test)
    {
        $this->test = $test;
    }

    public function setUpFixture(array $files, bool $sidebar = false): void
    {
        foreach ($files as $key => $file) {
            $class = $sidebar ? DocumentationPage::class : MarkdownPage::class;
            if (is_string($file)) {
                $page = new $class(basename($file, '.md'), markdown: '# '.str($file)->after('-')->before('.')->ucfirst()."\n\nHello, world!\n");
                Hyde::pages()->addPage($page);
                Hyde::routes()->addRoute($page->getRoute());
            } else {
                foreach ($file as $child) {
                    $group = str($key)->after('-');
                    $page = new $class($group.'/'.basename($child, '.md'), markdown: '# '.str($child)->after('-')->before('.')->ucfirst()."\n\nHello, world!\n");
                    Hyde::pages()->addPage($page);
                    Hyde::routes()->addRoute($page->getRoute());
                }
            }
        }
    }

    public function createComparisonFormat(bool $sidebar): array
    {
        $type = $sidebar ? DocumentationSidebar::class : MainNavigationMenu::class;
        $menu = NavigationMenuGenerator::handle($type);

        $formatRouteKey = fn (string $routeKey): string => $sidebar ? Str::after($routeKey, 'docs/') : $routeKey;

        return $menu->getItems()->mapWithKeys(function (NavigationItem|NavigationGroup $item, int $key) use ($formatRouteKey): array {
            if ($item instanceof NavigationGroup) {
                return [$item->getGroupKey() => $item->getItems()->map(function (NavigationItem $item) use ($formatRouteKey): string {
                    return basename($formatRouteKey($item->getPage()->getRouteKey()));
                })->all()];
            }

            return [$key => $formatRouteKey($item->getPage()->getRouteKey())];
        })->all();
    }
}
