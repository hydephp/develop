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
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;
use Hyde\Framework\Features\XmlGenerators\SitemapPage;
use Illuminate\Support\Facades\File;

/**
 * Feature test for the sitemap page, covering its registration through the core
 * extension, compilation through the standard build, and the user-land override
 * and container rebind customization paths.
 *
 * @see \Hyde\Framework\Testing\Feature\SitemapFeatureTest
 * @see \Hyde\Framework\Testing\Feature\Services\SitemapServiceTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\XmlGenerators\SitemapPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeCoreExtension::class)]
class SitemapPageTest extends TestCase
{
    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testSitemapPageIsRegisteredAsRouteWhenSitemapFeatureIsEnabled()
    {
        $this->withSiteUrl();

        $this->assertTrue(Routes::exists('sitemap.xml'));

        $page = Routes::get('sitemap.xml')->getPage();

        $this->assertInstanceOf(SitemapPage::class, $page);
        $this->assertSame('sitemap.xml', $page->getOutputPath());
        $this->assertSame($page::outputPath($page->getIdentifier()), $page->getOutputPath());
        $this->assertSame('sitemap.xml', $page->getRouteKey());
    }

    public function testSitemapPageIsNotRegisteredWithoutSiteUrl()
    {
        $this->withoutSiteUrl();

        $this->assertFalse(Routes::exists('sitemap.xml'));
    }

    public function testSitemapPageIsNotRegisteredWhenSitemapIsDisabledInConfig()
    {
        $this->withSiteUrl();
        config(['hyde.generate_sitemap' => false]);

        $this->assertFalse(Routes::exists('sitemap.xml'));
    }

    public function testSitemapPageIsHiddenFromNavigationAndExcludesItselfFromTheSitemap()
    {
        $page = new SitemapPage();

        $this->assertFalse($page->showInNavigation());
        $this->assertFalse($page->showInSitemap());
    }

    public function testSitemapPageCompilesUsingTheSitemapGenerator()
    {
        $this->withSiteUrl();

        $contents = (new SitemapPage())->compile();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $contents);
        $this->assertStringContainsString('<urlset', $contents);
    }

    public function testSitemapGeneratorCanBeSwappedThroughTheServiceContainer()
    {
        $this->withSiteUrl();

        app()->bind(SitemapGenerator::class, fn (): SitemapGenerator => new class extends SitemapGenerator
        {
            public function generate(): static
            {
                return $this;
            }

            public function getXml(): string
            {
                return 'custom generator output';
            }
        });

        $this->assertSame('custom generator output', Routes::get('sitemap.xml')->getPage()->compile());
    }

    public function testBuildCommandCompilesSitemapPageAsDynamicPage()
    {
        $this->withSiteUrl();

        $this->artisan('build')
            ->expectsOutput('Creating Dynamic Pages...')
            ->assertExitCode(0);

        $contents = file_get_contents(Hyde::path('_site/sitemap.xml'));

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $contents);
        $this->assertStringContainsString('<urlset', $contents);
        $this->assertStringNotContainsString('sitemap.xml', $contents);
    }

    public function testSitemapPageIsIncludedInTheBuildManifest()
    {
        $this->withSiteUrl();

        $this->artisan('build')->assertExitCode(0);

        $manifest = json_decode(file_get_contents(Hyde::path('app/storage/framework/cache/build-manifest.json')), true);

        $this->assertArrayHasKey('sitemap.xml', $manifest['pages']);
        $this->assertSame('sitemap.xml', $manifest['pages']['sitemap.xml']['output_path']);
    }

    public function testSitemapRouteIsIncludedInTheRouteList()
    {
        $this->withSiteUrl();

        $this->artisan('route:list')
            ->expectsOutputToContain('sitemap.xml')
            ->assertExitCode(0);
    }

    public function testUserPageRegisteredInBootingCallbackSuppressesTheGeneratedSitemapPage()
    {
        $this->withSiteUrl();

        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('sitemap.xml', contents: 'user defined sitemap'));
        });

        $page = Routes::get('sitemap.xml')->getPage();

        $this->assertNotInstanceOf(SitemapPage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'sitemap.xml')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('user defined sitemap', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }

    public function testUserPageRegisteredThroughExtensionSuppressesTheGeneratedSitemapPage()
    {
        $this->withSiteUrl();

        Hyde::kernel()->registerExtension(SitemapPageTestExtension::class);

        $page = Routes::get('sitemap.xml')->getPage();

        $this->assertNotInstanceOf(SitemapPage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'sitemap.xml')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('extension defined sitemap', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }
}

class SitemapPageTestExtension extends HydeExtension
{
    public function discoverPages(PageCollection $collection): void
    {
        $collection->addPage(InMemoryPage::make('sitemap.xml', contents: 'extension defined sitemap'));
    }
}
