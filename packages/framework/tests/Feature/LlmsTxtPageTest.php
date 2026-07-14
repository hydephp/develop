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
use Hyde\Framework\Features\TextGenerators\LlmsTxtGenerator;
use Hyde\Framework\Features\TextGenerators\LlmsTxtPage;
use Illuminate\Support\Facades\File;

/**
 * Feature test for the llms.txt page, covering its registration through the core
 * extension, compilation through the standard build, and the user-land override
 * and container rebind customization paths.
 *
 * @see \Hyde\Framework\Testing\Feature\LlmsTxtGeneratorTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\TextGenerators\LlmsTxtPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeCoreExtension::class)]
class LlmsTxtPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withSiteUrl();
    }

    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testLlmsTxtPageIsRegisteredAsRouteByDefault()
    {
        $this->assertTrue(Routes::exists('llms.txt'));

        $page = Routes::get('llms.txt')->getPage();

        $this->assertInstanceOf(LlmsTxtPage::class, $page);
        $this->assertSame('llms.txt', $page->getOutputPath());
        $this->assertSame('llms.txt', $page->getRouteKey());
    }

    public function testLlmsTxtPageIsNotRegisteredWithoutSiteUrl()
    {
        $this->withoutSiteUrl();

        $this->assertFalse(Routes::exists('llms.txt'));
    }

    public function testLlmsTxtPageIsNotRegisteredWhenDisabledInConfig()
    {
        config(['hyde.llms.enabled' => false]);

        $this->assertFalse(Routes::exists('llms.txt'));
    }

    public function testLlmsTxtPageIsHiddenFromNavigationAndExcludedFromTheSitemap()
    {
        $page = new LlmsTxtPage();

        $this->assertFalse($page->showInNavigation());
        $this->assertFalse($page->showInSitemap());
    }

    public function testLlmsTxtPageCompilesUsingTheLlmsTxtGenerator()
    {
        $this->assertSame((new LlmsTxtGenerator())->generate(), (new LlmsTxtPage())->compile());
    }

    public function testLlmsTxtGeneratorCanBeSwappedThroughTheServiceContainer()
    {
        app()->bind(LlmsTxtGenerator::class, fn (): LlmsTxtGenerator => new class extends LlmsTxtGenerator
        {
            public function generate(): string
            {
                return 'custom generator output';
            }
        });

        $this->assertSame('custom generator output', Routes::get('llms.txt')->getPage()->compile());
    }

    public function testBuildCommandCompilesLlmsTxtPageAsDynamicPage()
    {
        $this->artisan('build')
            ->expectsOutput('Creating Dynamic Pages...')
            ->assertExitCode(0);

        $this->assertStringStartsWith('# HydePHP', file_get_contents(Hyde::path('_site/llms.txt')));
    }

    public function testBuiltLlmsTxtIsExcludedFromTheSitemapAndItself()
    {
        $this->artisan('build')->assertExitCode(0);

        $this->assertStringNotContainsString('llms.txt', file_get_contents(Hyde::path('_site/sitemap.xml')));
        $this->assertStringNotContainsString('llms.txt', file_get_contents(Hyde::path('_site/llms.txt')));
    }

    public function testLlmsTxtPageIsIncludedInTheBuildManifest()
    {
        $this->artisan('build')->assertExitCode(0);

        $manifest = json_decode(file_get_contents(Hyde::path('app/storage/framework/cache/build-manifest.json')), true);

        $this->assertArrayHasKey('llms.txt', $manifest['pages']);
        $this->assertSame('llms.txt', $manifest['pages']['llms.txt']['output_path']);
    }

    public function testLlmsTxtRouteIsIncludedInTheRouteList()
    {
        $this->artisan('route:list')
            ->expectsOutputToContain('llms.txt')
            ->assertExitCode(0);
    }

    public function testUserPageRegisteredInBootingCallbackSuppressesTheGeneratedLlmsTxtPage()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::file('llms.txt', contents: 'user defined llms'));
        });

        $page = Routes::get('llms.txt')->getPage();

        $this->assertNotInstanceOf(LlmsTxtPage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'llms.txt')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('user defined llms', file_get_contents(Hyde::path('_site/llms.txt')));
    }

    public function testUserPageRegisteredThroughExtensionSuppressesTheGeneratedLlmsTxtPage()
    {
        Hyde::kernel()->registerExtension(LlmsTxtPageTestExtension::class);

        $page = Routes::get('llms.txt')->getPage();

        $this->assertNotInstanceOf(LlmsTxtPage::class, $page);
        $this->assertSame(1, Hyde::pages()->filter(fn ($page) => $page->getRouteKey() === 'llms.txt')->count());

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('extension defined llms', file_get_contents(Hyde::path('_site/llms.txt')));
    }
}

class LlmsTxtPageTestExtension extends HydeExtension
{
    public function discoverPages(PageCollection $collection): void
    {
        $collection->addPage(InMemoryPage::file('llms.txt', contents: 'extension defined llms'));
    }
}
