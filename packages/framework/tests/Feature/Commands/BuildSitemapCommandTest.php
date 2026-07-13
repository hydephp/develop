<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\BuildSitemapCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\XmlGenerators\SitemapPage::class)]
class BuildSitemapCommandTest extends TestCase
{
    public function testSitemapIsGeneratedWhenConditionsAreMet()
    {
        config(['hyde.url' => 'https://example.com']);

        $this->cleanUpWhenDone('_site/sitemap.xml');

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')
            ->expectsOutputToContain('Created [_site/sitemap.xml]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));
    }

    public function testSitemapIsNotGeneratedWhenConditionsAreNotMet()
    {
        config(['hyde.url' => '']);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap without a valid base URL')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function testSitemapIsNotGeneratedWhenSitemapGenerationIsDisabledInConfig()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => false]);

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate the sitemap as it is disabled in the configuration')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function testCommandBuildsUserDefinedSitemapPageWhenOneIsRegistered()
    {
        config(['hyde.url' => 'https://example.com']);

        $this->cleanUpWhenDone('_site/sitemap.xml');

        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('sitemap.xml', contents: '<?xml version="1.0"?><urlset/>'));
        });

        $this->artisan('build:sitemap')->assertExitCode(0);

        $this->assertSame('<?xml version="1.0"?><urlset/>', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }

    public function testCommandBuildsUserDefinedSitemapPageEvenWhenSitemapFeatureIsDisabled()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => false]);

        $this->cleanUpWhenDone('_site/sitemap.xml');

        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('sitemap.xml', contents: '<?xml version="1.0"?><urlset/>'));
        });

        $this->artisan('build:sitemap')->assertExitCode(0);

        $this->assertSame('<?xml version="1.0"?><urlset/>', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }
}
