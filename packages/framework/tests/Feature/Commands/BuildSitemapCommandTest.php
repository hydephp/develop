<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\BuildSitemapCommand::class)]
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
            ->expectsOutput('Cannot generate the sitemap as the feature is not enabled')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function testSitemapIsNotGeneratedWhenSitemapGenerationIsDisabledInConfig()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => false]);

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate the sitemap as the feature is not enabled')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }
}
