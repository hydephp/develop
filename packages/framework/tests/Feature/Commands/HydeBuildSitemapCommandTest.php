<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildSitemapCommand
 */
class HydeBuildSitemapCommandTest extends TestCase
{
    public function test_sitemap_is_not_generated_when_conditions_are_not_met()
    {
        config(['hyde.site_url' => '']);
        config(['hyde.generate_sitemap' => false]);
        unlinkIfExists(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap.xml, please check your configuration.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function test_sitemap_is_generated_when_conditions_are_met()
    {
        config(['hyde.site_url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => true]);

        unlinkIfExists(Hyde::path('_site/sitemap.xml'));
        $this->artisan('build:sitemap')
            ->expectsOutput('Generating sitemap...')
            ->expectsOutputToContain('Created sitemap.xml')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));
        unlink(Hyde::path('_site/sitemap.xml'));
    }
}
