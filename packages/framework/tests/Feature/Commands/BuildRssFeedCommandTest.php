<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildRssFeedCommand
 * @covers \Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateRssFeed
 */
class BuildRssFeedCommandTest extends TestCase
{
    public function test_rss_feed_is_generated_when_conditions_are_met()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_rss_feed' => true]);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/feed.xml'));
        Hyde::unlink('_site/feed.xml');
    }

    public function test_rss_filename_can_be_changed()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.generate_rss_feed' => true]);
        config(['hyde.rss_filename' => 'blog.xml']);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileDoesNotExist(Hyde::path('_site/blog.xml'));

        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileExists(Hyde::path('_site/blog.xml'));
        Hyde::unlink('_site/blog.xml');
    }
}
