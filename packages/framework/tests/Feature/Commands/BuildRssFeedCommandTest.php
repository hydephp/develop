<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildRssFeedCommand
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed
 */
class BuildRssFeedCommandTest extends TestCase
{
    public function test_rss_feed_is_generated_when_conditions_are_met()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/feed.xml'));
        Filesystem::unlink('_site/feed.xml');
    }

    public function test_rss_filename_can_be_changed()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.rss.enabled' => true]);
        config(['hyde.rss_filename' => 'blog.xml']);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileDoesNotExist(Hyde::path('_site/blog.xml'));

        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileExists(Hyde::path('_site/blog.xml'));
        Filesystem::unlink('_site/blog.xml');
    }
}
