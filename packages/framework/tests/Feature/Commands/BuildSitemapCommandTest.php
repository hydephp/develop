<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildSitemapCommand
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 */
class BuildSitemapCommandTest extends TestCase
{
    public function testSitemapIsGeneratedWhenConditionsAreMet()
    {
        $this->withSiteUrl();

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));

        Filesystem::unlink('_site/sitemap.xml');
    }

    public function testSitemapIsNotGeneratedWhenConditionsAreNotMet()
    {
        $this->withoutSiteUrl();

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')
            ->expectsOutputToContain('Generating sitemap...')
            ->expectsOutputToContain('Skipped')
            ->expectsOutput(' > Cannot generate sitemap without a valid base URL')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }
}
