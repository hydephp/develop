<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
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
 * Todo: Add test to ensure explicitly set priority overrides filename prefix.
 */
class FilenamePrefixNavigationPriorityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    protected function setUpFixture(array $files): self
    {
        foreach ($files as $key => $file) {
            if (is_string($file)) {
                $page = new MarkdownPage(basename($file, '.md'), markdown: '# '.str($file)->after('-')->before('.')->ucfirst()."\n\nHello, world!\n");
                Hyde::pages()->addPage($page);
                Hyde::routes()->addRoute($page->getRoute());
            } else {
                foreach ($file as $child) {
                    $group = str($key)->after('-');
                    $page = new MarkdownPage($group.'/'.basename($child, '.md'), markdown: '# '.str($child)->after('-')->before('.')->ucfirst()."\n\nHello, world!\n");
                    Hyde::pages()->addPage($page);
                    Hyde::routes()->addRoute($page->getRoute());
                }
            }
        }

        return $this;
    }

    /** @param array<string> $expected */
    protected function assertOrder(array $expected): void
    {
        $menu = NavigationMenuGenerator::handle(MainNavigationMenu::class);
        $actual = $menu->getItems()->map(function (NavigationItem|NavigationGroup $item) {
            if ($item instanceof NavigationGroup) {
                return $item->getItems()->map(fn ($item) => $item->getPage()->getRouteKey())->all();
            }
            return $item->getPage()->getRouteKey();
        })->all();

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
                '01-features.md',
                '02-extensions.md',
                '03-configuration.md',
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

    public function fixtureFileExtensions(): array
    {
        return [
            '01-foo.md',
            '02-bar.html',
            '03-baz.blade.php',
        ];
    }
}
