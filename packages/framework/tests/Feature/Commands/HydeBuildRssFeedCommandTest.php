<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeBuildRssFeedCommand
 *
 * @todo Add output tests like @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSitemapCommandTest
 */
class HydeBuildRssFeedCommandTest extends TestCase
{
    public function test_rss_feed_is_not_generated_when_conditions_are_not_met()
    {
        config(['site.site_url' => '']);
        config(['hyde.generate_rss_feed' => false]);

        unlinkIfExists(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
    }

    public function test_rss_feed_is_generated_when_conditions_are_met()
    {
        config(['site.site_url' => 'https://example.com']);
        config(['hyde.generate_rss_feed' => true]);

        unlinkIfExists(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')
            ->expectsOutput('Generating RSS feed...')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/feed.xml'));
        unlink(Hyde::path('_site/feed.xml'));
    }

    public function test_rss_filename_can_be_changed()
    {
        config(['site.site_url' => 'https://example.com']);
        config(['hyde.generate_rss_feed' => true]);
        config(['hyde.rss_filename' => 'blog.xml']);

        unlinkIfExists(Hyde::path('_site/feed.xml'));
        unlinkIfExists(Hyde::path('_site/blog.xml'));

        $this->artisan('build:rss')
            ->expectsOutput('Generating RSS feed...')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileExists(Hyde::path('_site/blog.xml'));
        unlink(Hyde::path('_site/blog.xml'));
    }
}
