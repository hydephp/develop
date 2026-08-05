<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Facades\Filesystem;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;
use Illuminate\Support\Facades\File;

/**
 * Feature test for the RSS feed page, covering its registration through the core
 * extension, compilation through the standard build, and the user-land override
 * and container rebind customization paths.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\RssFeedServiceTest
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildRssFeedCommandTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeCoreExtension::class)]
class RssFeedPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withSiteUrl();
        $this->file('_posts/hello-world.md', "# Hello, World!\n\nThis is the first post.");
    }

    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testFeedPageIsRegisteredAsRouteWhenRssFeatureIsEnabled()
    {
        $this->assertTrue(Routes::exists('feed.xml'));

        $page = Routes::get('feed.xml')->getPage();

        $this->assertSame(InMemoryPage::class, $page::class);
        $this->assertSame('feed.xml', $page->getOutputPath());
        $this->assertSame($page::outputPath($page->getIdentifier()), $page->getOutputPath());
        $this->assertSame('feed.xml', $page->getRouteKey());
    }

    public function testFeedPageIsNotRegisteredWithoutSiteUrl()
    {
        $this->withoutSiteUrl();

        $this->assertFalse(Routes::exists('feed.xml'));
    }

    public function testFeedPageIsNotRegisteredWhenThereAreNoPosts()
    {
        Filesystem::unlink('_posts/hello-world.md');

        $this->assertFalse(Routes::exists('feed.xml'));
    }

    public function testFeedPageIsNotRegisteredWhenRssIsDisabledInConfig()
    {
        config(['hyde.rss.enabled' => false]);

        $this->assertFalse(Routes::exists('feed.xml'));
    }

    public function testFeedPageUsesConfiguredFilenameAsRouteKey()
    {
        config(['hyde.rss.filename' => 'blog.xml']);

        $this->assertFalse(Routes::exists('feed.xml'));
        $this->assertTrue(Routes::exists('blog.xml'));
        $this->assertSame('blog.xml', Routes::get('blog.xml')->getPage()->getOutputPath());
    }

    public function testFeedPageUsesConfiguredFilenameVerbatimForAnyExtension()
    {
        config(['hyde.rss.filename' => 'feed.rss']);

        $this->assertTrue(Routes::exists('feed.rss'));
        $this->assertSame('feed.rss', Routes::get('feed.rss')->getPage()->getOutputPath());
        $this->assertSame('feed.rss', InMemoryPage::outputPath('feed.rss'));
    }

    public function testFeedPageIsHiddenFromNavigationAndExcludesItselfFromTheSitemap()
    {
        $page = Routes::get('feed.xml')->getPage();

        $this->assertFalse($page->showInNavigation());
        $this->assertFalse($page->showInSitemap());
    }

    public function testFeedPageCompilesUsingTheRssFeedGenerator()
    {
        $contents = Routes::get('feed.xml')->getPage()->compile();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $contents);
        $this->assertStringContainsString('<rss', $contents);
        $this->assertStringContainsString('version="2.0"', $contents);
        $this->assertStringContainsString('Hello, World!', $contents);
    }

    public function testRssFeedGeneratorCanBeSwappedThroughTheServiceContainer()
    {
        app()->bind(RssFeedGenerator::class, fn (): RssFeedGenerator => new class extends RssFeedGenerator
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

        $this->assertSame('custom generator output', Routes::get('feed.xml')->getPage()->compile());
    }

    public function testBuildCommandCompilesFeedPageAsDynamicPage()
    {
        $this->artisan('build')
            ->expectsOutput('Creating Dynamic Pages...')
            ->assertExitCode(0);

        $contents = file_get_contents(Hyde::path('_site/feed.xml'));

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $contents);
        $this->assertStringContainsString('<rss', $contents);
        $this->assertStringContainsString('version="2.0"', $contents);

        $this->assertStringNotContainsString('feed.xml', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }

    public function testFeedPageIsIncludedInTheBuildManifest()
    {
        $this->artisan('build')->assertExitCode(0);

        $manifest = json_decode(file_get_contents(Hyde::path('app/storage/framework/cache/build-manifest.json')), true);

        $this->assertArrayHasKey('feed.xml', $manifest['pages']);
        $this->assertSame('feed.xml', $manifest['pages']['feed.xml']['output_path']);
    }

    public function testFeedRouteIsIncludedInTheRouteList()
    {
        $this->artisan('route:list')
            ->expectsOutputToContain('feed.xml')
            ->assertExitCode(0);
    }

    public function testUserPageRegisteredInBootingCallbackSuppressesTheGeneratedFeedPage()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('feed.xml', contents: 'user defined feed'));
        });

        $page = Routes::get('feed.xml')->getPage();

        $this->assertSame('user defined feed', $page->compile());
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'feed.xml')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('user defined feed', file_get_contents(Hyde::path('_site/feed.xml')));
    }

    public function testUserPageRegisteredThroughExtensionSuppressesTheGeneratedFeedPage()
    {
        Hyde::kernel()->registerExtension(RssFeedPageTestExtension::class);

        $page = Routes::get('feed.xml')->getPage();

        $this->assertSame('extension defined feed', $page->compile());
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'feed.xml')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('extension defined feed', file_get_contents(Hyde::path('_site/feed.xml')));
    }
}

class RssFeedPageTestExtension extends HydeExtension
{
    public function discoverPages(PageCollection $collection): void
    {
        $collection->addPage(InMemoryPage::make('feed.xml', contents: 'extension defined feed'));
    }
}
