<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Features\TextGenerators\RobotsTxtGenerator;
use Hyde\Framework\Features\GeneratedFiles\GeneratedFilePage;
use Hyde\Framework\Features\GeneratedFiles\GeneratedFileRegistry;
use Illuminate\Support\Facades\File;

/**
 * Feature test for the robots.txt page, covering its registration through the core
 * extension, compilation through the standard build, and the user-land override
 * and container rebind customization paths.
 *
 * @see \Hyde\Framework\Testing\Feature\RobotsTxtGeneratorTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\GeneratedFiles\GeneratedFilePage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\GeneratedFiles\GeneratedFileRegistry::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeCoreExtension::class)]
class RobotsTxtPageTest extends TestCase
{
    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testRobotsTxtPageIsRegisteredAsRouteByDefault()
    {
        $this->assertTrue(Routes::exists('robots.txt'));

        $page = Routes::get('robots.txt')->getPage();

        $this->assertInstanceOf(GeneratedFilePage::class, $page);
        $this->assertSame('robots.txt', $page->getOutputPath());
        $this->assertSame('robots.txt', $page->getRouteKey());
    }

    public function testRobotsTxtPageIsRegisteredWithoutSiteUrl()
    {
        $this->withoutSiteUrl();

        $this->assertTrue(Routes::exists('robots.txt'));
    }

    public function testRobotsTxtPageIsNotRegisteredWhenDisabledInConfig()
    {
        config(['hyde.robots.enabled' => false]);

        $this->assertFalse(Routes::exists('robots.txt'));
    }

    public function testRobotsTxtPageIsHiddenFromNavigationAndExcludedFromTheSitemap()
    {
        $page = new GeneratedFilePage(GeneratedFileRegistry::ROBOTS, RobotsTxtGenerator::class);

        $this->assertFalse($page->showInNavigation());
        $this->assertFalse($page->showInSitemap());
    }

    public function testRobotsTxtPageCompilesUsingTheRobotsTxtGenerator()
    {
        $this->withoutSiteUrl();

        $this->assertSame("User-agent: *\nAllow: /\n", (new GeneratedFilePage(GeneratedFileRegistry::ROBOTS, RobotsTxtGenerator::class))->compile());
    }

    public function testRobotsTxtGeneratorCanBeSwappedThroughTheServiceContainer()
    {
        app()->bind(RobotsTxtGenerator::class, fn (): RobotsTxtGenerator => new class extends RobotsTxtGenerator
        {
            public function generate(): string
            {
                return 'custom generator output';
            }
        });

        $this->assertSame('custom generator output', Routes::get('robots.txt')->getPage()->compile());
    }

    public function testBuildCommandCompilesRobotsTxtPageAsDynamicPage()
    {
        $this->withoutSiteUrl();

        $this->artisan('build')
            ->expectsOutput('Creating Dynamic Pages...')
            ->assertExitCode(0);

        $this->assertSame("User-agent: *\nAllow: /\n", file_get_contents(Hyde::path('_site/robots.txt')));
    }

    public function testBuiltRobotsTxtLinksToSitemapAndIsExcludedFromIt()
    {
        $this->withSiteUrl();

        $this->artisan('build')->assertExitCode(0);

        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', file_get_contents(Hyde::path('_site/robots.txt')));
        $this->assertStringNotContainsString('robots.txt', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }

    public function testRobotsTxtPageIsIncludedInTheBuildManifest()
    {
        $this->artisan('build')->assertExitCode(0);

        $manifest = json_decode(file_get_contents(Hyde::path('app/storage/framework/cache/build-manifest.json')), true);

        $this->assertArrayHasKey('robots.txt', $manifest['pages']);
        $this->assertSame('robots.txt', $manifest['pages']['robots.txt']['output_path']);
    }

    public function testRobotsTxtRouteIsIncludedInTheRouteList()
    {
        $this->artisan('route:list')
            ->expectsOutputToContain('robots.txt')
            ->assertExitCode(0);
    }

    public function testUserPageRegisteredInBootingCallbackSuppressesTheGeneratedRobotsTxtPage()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::file('robots.txt', contents: 'user defined robots'));
        });

        $page = Routes::get('robots.txt')->getPage();

        $this->assertNotInstanceOf(GeneratedFilePage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'robots.txt')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('user defined robots', file_get_contents(Hyde::path('_site/robots.txt')));
    }

    public function testUserPageRegisteredThroughExtensionSuppressesTheGeneratedRobotsTxtPage()
    {
        Hyde::kernel()->registerExtension(RobotsTxtPageTestExtension::class);

        $page = Routes::get('robots.txt')->getPage();

        $this->assertNotInstanceOf(GeneratedFilePage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'robots.txt')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('extension defined robots', file_get_contents(Hyde::path('_site/robots.txt')));
    }
}

class RobotsTxtPageTestExtension extends HydeExtension
{
    public function discoverPages(PageCollection $collection): void
    {
        $collection->addPage(InMemoryPage::file('robots.txt', contents: 'extension defined robots'));
    }
}
