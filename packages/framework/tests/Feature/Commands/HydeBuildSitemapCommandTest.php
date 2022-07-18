<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildSitemapCommand
 */
class HydeBuildSitemapCommandTest extends TestCase
{
    public function test_sitemap_is_generated_when_conditions_are_met()
    {
        config(['site.site_url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => true]);

        unlinkIfExists(Hyde::path('_site/sitemap.xml'));
        $this->artisan('build:sitemap')
            ->expectsOutput('Generating sitemap...')
            ->expectsOutputToContain('Created sitemap.xml')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));
        unlink(Hyde::path('_site/sitemap.xml'));
    }

    public function test_sitemap_is_not_generated_when_conditions_are_not_met()
    {
        config(['site.site_url' => '']);
        config(['hyde.generate_sitemap' => false]);
        unlinkIfExists(Hyde::path('_site/sitemap.xml'));

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap.xml, please check your configuration.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function test_sitemap_returns_helpful_error_message_when_no_site_url_is_configured()
    {
        config(['site.site_url' => '']);
        config(['hyde.generate_sitemap' => true]);

        unlinkIfExists(Hyde::path('_site/sitemap.xml'));
        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap.xml, please check your configuration.')
            ->expectsOutputToContain('You don\'t have a site URL configured. Check config/hyde.php')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/sitemap.xml'));
    }

    public function test_sitemap_returns_helpful_error_message_when_sitemap_generation_is_disabled()
    {
        config(['site.site_url' => 'https://example.com']);
        config(['hyde.generate_sitemap' => false]);

        unlinkIfExists(Hyde::path('_site/sitemap.xml'));
        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap.xml, please check your configuration.')
            ->expectsOutputToContain('You have disabled sitemap generation in config/hyde.php')
            ->expectsOutputToContain('You can enable sitemap generation by setting `hyde.generate_sitemap` to `true`')
            ->assertExitCode(1);
    }

    public function test_sitemap_returns_helpful_error_message_when_simplexml_is_not_installed()
    {
        config(['testing.mock_disabled_extensions' => true]);

        $this->artisan('build:sitemap')
            ->expectsOutput('Cannot generate sitemap.xml, please check your configuration.')
            ->expectsOutputToContain('You don\'t have the `simplexml` extension installed. Check your PHP installation.')
            ->assertExitCode(1);
    }
}
